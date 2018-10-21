<?php declare(strict_types=1);

namespace Rector\ContributorTools\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Console\ConsoleStyle;
use Rector\ContributorTools\Configuration\Configuration;
use Rector\ContributorTools\Configuration\ConfigurationFactory;
use Rector\Printer\BetterStandardPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\FileSystem\FinderSanitizer;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use function Safe\getcwd;
use function Safe\sort;
use function Safe\sprintf;

final class CreateRectorCommand extends Command
{
    /**
     * @var string
     */
    private const TEMPLATES_DIRECTORY = __DIR__ . '/../../templates';

    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    /**
     * @var string[]
     */
    private $generatedFiles = [];

    public function __construct(
        ConsoleStyle $consoleStyle,
        ConfigurationFactory $configurationFactory,
        BetterStandardPrinter $betterStandardPrinter,
        FinderSanitizer $finderSanitizer
    ) {
        parent::__construct();
        $this->consoleStyle = $consoleStyle;
        $this->configurationFactory = $configurationFactory;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->finderSanitizer = $finderSanitizer;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Create a new Rector, in proper location, with new tests');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationFactory->createFromConfigFile(getcwd() . '/create-rector.yml');
        $data = $this->prepareData($configuration);

        $testCasePath = null;

        foreach ($this->findTemplateFileInfos() as $smartFileInfo) {
            $destination = $smartFileInfo->getRelativeFilePathFromDirectory(self::TEMPLATES_DIRECTORY);
            $destination = $this->applyData($destination, $data);

            $content = $this->applyData($smartFileInfo->getContents(), $data);
            FileSystem::write($destination, $content);

            $this->generatedFiles[] = $destination;

            if (! $testCasePath && Strings::endsWith($destination, 'Test.php')) {
                $testCasePath = dirname($destination);
            }
        }

        $this->printSuccess($configuration, $testCasePath);

        return ShellCode::SUCCESS;
    }

    /**
     * @return mixed[]
     */
    private function prepareData(Configuration $configuration): array
    {
        $data = [
            '_Package_' => $configuration->getPackage(),
            '_Category_' => $configuration->getCategory(),
            '_Description_' => $configuration->getDescription(),
            '_Name_' => $configuration->getName(),
            '_CodeBefore_' => trim($configuration->getCodeBefore()) . PHP_EOL,
            '_CodeBeforeExample_' => $this->prepareCodeForDefinition($configuration->getCodeBefore()),
            '_CodeAfter_' => trim($configuration->getCodeAfter()) . PHP_EOL,
            '_CodeAfterExample_' => $this->prepareCodeForDefinition($configuration->getCodeAfter()),
            '_Source_' => $this->prepareSourceDocBlock($configuration->getSource()),
        ];

        $arrayNodes = [];
        foreach ($configuration->getNodeTypes() as $nodeType) {
            $arrayNodes[] = new ArrayItem(new ClassConstFetch(new FullyQualified($nodeType), 'class'));
        }
        $data['_NodeTypes_Php_'] = $this->betterStandardPrinter->prettyPrint([new Array_($arrayNodes)]);

        $data['_NodeTypes_Doc_'] = '\\' . implode('|\\', $configuration->getNodeTypes());

        return $data;
    }

    /**
     * @param mixed[] $data
     */
    private function applyData(string $content, array $data): string
    {
        return str_replace(array_keys($data), array_values($data), $content);
    }

    private function prepareCodeForDefinition(string $code): string
    {
        if (Strings::contains($code, PHP_EOL)) {
            // multi lines
            return sprintf("<<<'CODE_SAMPLE'%s%sCODE_SAMPLE%s", PHP_EOL, $code, PHP_EOL);
        }

        // single line
        return "'" . str_replace("'", '"', $code) . "'";
    }

    private function prepareSourceDocBlock(string $source): string
    {
        if (! $source) {
            return $source;
        }

        $sourceDocBlock = <<<'CODE_SAMPLE'
/**
 * @see %s
 */
CODE_SAMPLE;

        return sprintf($sourceDocBlock, $source);
    }

    /**
     * @return SmartFileInfo[]
     */
    private function findTemplateFileInfos(): array
    {
        $finder = Finder::create()->files()
            ->in(self::TEMPLATES_DIRECTORY);

        return $this->finderSanitizer->sanitize($finder);
    }

    private function printSuccess(Configuration $configuration, string $testCasePath): void
    {
        $this->consoleStyle->title(sprintf('New files generated for "%s"', $configuration->getName()));
        sort($this->generatedFiles);
        $this->consoleStyle->listing($this->generatedFiles);

        $this->consoleStyle->success(sprintf(
            'Now make these tests green again:%svendor/bin/phpunit %s',
            PHP_EOL,
            $testCasePath
        ));
    }
}
