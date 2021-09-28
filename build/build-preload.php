<?php

// inspired at https://github.com/phpstan/phpstan-src/commit/87897c2a4980d68efa1c46049ac2eefe767ec946#diff-e897e523125a694bd8ea69bf83374c206803c98720c46d7401b7a7cf53915a26

declare(strict_types=1);

use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

require __DIR__ . '/../vendor/autoload.php';

$buildDirectory = $argv[1];

$symfonyStyleFactory = new SymfonyStyleFactory();
$symfonyStyle = $symfonyStyleFactory->create();

if (! is_string($buildDirectory)) {
    $errorMessage = 'Provide build directory path as an argument, e.g. "php build-preload.php rector-build-directory"';
    $symfonyStyle->error($errorMessage);
    exit(Command::FAILURE);
}

$preloadBuilder = new PreloadBuilder();
$preloadBuilder->buildPreloadScript($buildDirectory);

final class PreloadBuilder
{
    /**
     * @var string
     */
    private const PRELOAD_FILE_TEMPLATE = <<<'PHP'
<?php

declare(strict_types=1);


PHP;

    /**
     * @var int
     */
    private const PRIORITY_LESS_FILE_POSITION = -1;

    /**
     * These files are parent to another files, so they have to be included first
     * See https://github.com/rectorphp/rector/issues/6709 for more
     *
     * @var string[]
     */
    private  const HIGH_PRIORITY_FILES = [
        'Node.php',
        'NodeAbstract.php',
        'Expr.php',
        'NodeVisitor.php',
        'NodeVisitorAbstract.php',
        'Lexer.php',
        'TokenEmulator.php',
        'KeywordEmulator.php',
        'Comment.php',
        'PrettyPrinterAbstract.php',
        'Parser.php',
        'ParserAbstract.php',
        'ErrorHandler.php',
        'Stmt.php',
        'FunctionLike.php',
        'ClassLike.php',
        'Builder.php',
        'TraitUseAdaptation.php',
        'ComplexType.php',
        'CallLike.php',
        'AssignOp.php',
        'BinaryOp.php',
        'Name.php',
        'Scalar.php',
        'MagicConst.php',
        'NodeTraverserInterface.php',
        'Declaration.php',
        'Builder/FunctionLike.php',
        'Stmt/FunctionLike.php',
    ];

    public function buildPreloadScript(string $buildDirectory): void
    {
        $vendorDir = $buildDirectory . '/vendor';
        if (! is_dir($vendorDir . '/nikic/php-parser/lib/PhpParser')) {
            return;
        }

        // 1. fine php-parser file infos
        $fileInfos = $this->findPhpParserFiles($vendorDir);


        // append ContainerConfiguration to avoid accidental load of prefixed one from another tool
        $fileInfos[] = new SplFileInfo(__DIR__ . '/../vendor/symfony/dependency-injection/Loader/Configurator/AbstractConfigurator.php');
        $fileInfos[] = new SplFileInfo(__DIR__ . '/../vendor/symfony/dependency-injection/Loader/Configurator/ContainerConfigurator.php');

        // 2. put first-class usages first
        usort($fileInfos, function (SplFileInfo $firstFileInfo, SplFileInfo $secondFileInfo) {
            $firstFilePosition = $this->matchFilePriorityPosition($firstFileInfo);
            $secondFilePosition = $this->matchFilePriorityPosition($secondFileInfo);

            return $secondFilePosition <=> $firstFilePosition;
        });

        // 3. create preload.php from provided files
        $preloadFileContent = $this->createPreloadFileContent($fileInfos);

        file_put_contents($buildDirectory . '/preload.php', $preloadFileContent);
    }

    /**
     * @return SplFileInfo[]
     */
    private function findPhpParserFiles(string $vendorDir): array
    {
        $finder = (new Finder())
            ->files()
            ->name('*.php')
            ->in($vendorDir . '/nikic/php-parser/lib/PhpParser')
            ->notPath('#\/tests\/#')
            ->notPath('#\/config\/#')
            ->notPath('#\/set\/#')
            ->in($vendorDir . '/symplify/symfony-php-config')
            ->sortByName();

        return iterator_to_array($finder->getIterator());
    }

    /**
     * @param SplFileInfo[] $fileInfos
     */
    private function createPreloadFileContent(array $fileInfos): string
    {
        $preloadFileContent = self::PRELOAD_FILE_TEMPLATE;

        foreach ($fileInfos as $fileInfo) {
            $realPath = $fileInfo->getRealPath();
            if ($realPath === false) {
                continue;
            }

            $preloadFileContent .= $this->createRequireOnceFilePathLine($realPath);
        }

        return $preloadFileContent;
    }

    private function createRequireOnceFilePathLine(string $realPath): string
    {
        $filePath = '/vendor/' . Strings::after($realPath, 'vendor/');
        return "require_once __DIR__ . '" . $filePath . "';" . PHP_EOL;
    }

    private function matchFilePriorityPosition(SplFileInfo $splFileInfo): int
    {
        // to make <=> operator work
        $highPriorityFiles = array_reverse(self::HIGH_PRIORITY_FILES);

        $fileRealPath = $splFileInfo->getRealPath();

        foreach ($highPriorityFiles as $position => $highPriorityFile) {
            if (str_ends_with($fileRealPath, '/' . $highPriorityFile)) {
                return $position;
            }
        }

        return self::PRIORITY_LESS_FILE_POSITION;
    }
}
