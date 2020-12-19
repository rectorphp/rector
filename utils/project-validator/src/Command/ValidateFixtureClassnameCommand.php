<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Nette\Utils\Strings;
use const PATHINFO_DIRNAME;
use Rector\Core\Configuration\Option;
use Rector\Core\Util\StaticRectorStrings;
use Rector\PSR4\Composer\PSR4AutoloadPathsProvider;
use Rector\Utils\ProjectValidator\Finder\FixtureFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ValidateFixtureClassnameCommand extends Command
{
    /**
     * @var string
     * @see https://regex101.com/r/5KtBi8/2
     */
    private const NAMESPACE_REGEX = '#^namespace (.*);$#msU';

    /**
     * @var string
     * @see https://regex101.com/r/IDSGdI/6
     */
    private const CLASS_REGEX = '#(class) (\w+)\s+{$#msU';

    /**
     * @var string
     * @see https://regex101.com/r/yv2Rul/4
     */
    private const CLASS_WITH_EXTENDS_IMPLEMENTS_REGEX = '#(class) (\w+)\s+(extends|implements)\s+(.*)\s+\{$#msU';

    /**
     * @var string
     * @see https://regex101.com/r/T5LUbA/6
     */
    private const CLASS_USE_TRAIT_REGEX = '#^\s{0,}use\s+(.*);$#msU';

    /**
     * @var string[]
     */
    private const EXCLUDE_NAMES = [
        'string',
        'false',
        'resource',
        'mixed',
        'git_wrapper',
        'this',
        'object',
        'array_item',
    ];

    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var array<string, string>|array<string, string[]>
     */
    private $psr4autoloadPaths;

    /**
     * @var string
     */
    private $currentDirectory;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var FixtureFinder
     */
    private $fixtureFinder;

    public function __construct(
        FinderSanitizer $finderSanitizer,
        PSR4AutoloadPathsProvider $psr4AutoloadPathsProvider,
        SymfonyStyle $symfonyStyle,
        SmartFileSystem $smartFileSystem,
        FixtureFinder $fixtureFinder
    ) {
        $this->finderSanitizer = $finderSanitizer;
        $this->symfonyStyle = $symfonyStyle;
        $this->psr4autoloadPaths = $psr4AutoloadPathsProvider->provide();
        $this->currentDirectory = getcwd();
        $this->smartFileSystem = $smartFileSystem;

        parent::__construct();

        $this->fixtureFinder = $fixtureFinder;
    }

    protected function configure(): void
    {
        $this->addOption(Option::FIX, null, null, 'Fix found violations.');
        $this->setDescription('[CI] Validate tests fixtures class name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionFix = (bool) $input->getOption(Option::FIX);

        $fixtureFileInfos = $this->fixtureFinder->findFixtureFileInfos();
        $incorrectClassNameFiles = [];

        foreach ($fixtureFileInfos as $fixtureFileInfo) {
            // 1. geting expected namespace ...
            $paths = explode('/tests/', (string) $fixtureFileInfo);
            if (count($paths) > 2) {
                continue;
            }

            $path = ltrim(substr($paths[0], strlen($this->currentDirectory)) . '/tests', '/');
            $expectedNamespace = $this->getExpectedNamespace($path, $paths[1]);

            if ($expectedNamespace === null) {
                continue;
            }

            // 2. reading file contents
            $fileContent = $this->smartFileSystem->readFile((string) $fixtureFileInfo);
            $matchAll = Strings::matchAll($fileContent, self::NAMESPACE_REGEX);

            if (! $this->isFoundCorrectNamespace($matchAll, $expectedNamespace)) {
                continue;
            }

            $incorrectClassNameFiles = $this->checkAndFixClassName(
                $fileContent,
                $fixtureFileInfo,
                $incorrectClassNameFiles,
                $expectedNamespace,
                $optionFix
            );
        }

        if ($incorrectClassNameFiles !== []) {
            $this->symfonyStyle->listing($incorrectClassNameFiles);

            $message = sprintf(
                'Found %d fixture files with invalid class name which not follow psr-4 defined in composer.json',
                count($incorrectClassNameFiles)
            );

            if (! $optionFix) {
                $message .= '. Just add "--fix" to console command and rerun to apply.';
                $this->symfonyStyle->error($message);
                return ShellCode::ERROR;
            }

            $this->symfonyStyle->success($message . ' and all fixtures are corrected', );
            return ShellCode::SUCCESS;
        }

        $this->symfonyStyle->success('All fixtures are correct');
        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $incorrectClassNameFiles
     * @return string[]
     */
    private function checkAndFixClassName(
        string $fileContent,
        SmartFileInfo $fixtureFile,
        array $incorrectClassNameFiles,
        string $expectedNamespace,
        bool $optionFix
    ): array {
        $matchAll = Strings::matchAll($fileContent, self::CLASS_REGEX);

        if ($matchAll === [] || count($matchAll) > 2) {
            return $incorrectClassNameFiles;
        }

        $fileName = substr($fixtureFile->getFileName(), 0, -8);

        if (in_array($fileName, self::EXCLUDE_NAMES, true)) {
            return $incorrectClassNameFiles;
        }

        $hasTrait = (bool) Strings::match($fileContent, self::CLASS_USE_TRAIT_REGEX);
        if ($hasTrait) {
            return $incorrectClassNameFiles;
        }

        $fileName = str_replace('-', '_', $fileName);
        $expectedClassName = ucfirst(StaticRectorStrings::uppercaseUnderscoreToCamelCase($fileName));
        $incorrectClassName = $this->getClassName($matchAll);
        if ($expectedClassName === $incorrectClassName) {
            return $incorrectClassNameFiles;
        }

        $hasExtendsImplements = (bool) Strings::match($fileContent, self::CLASS_WITH_EXTENDS_IMPLEMENTS_REGEX);
        if ($hasExtendsImplements) {
            return $incorrectClassNameFiles;
        }

        $incorrectClassNameFiles[] = (string) $fixtureFile;

        if ($optionFix) {
            $this->fixClassName((string) $fixtureFile, $incorrectClassName, $fileContent, $expectedClassName);
        }

        return $incorrectClassNameFiles;
    }

    private function fixClassName(
        string $incorrectClassNameFile,
        string $incorrectClassName,
        string $incorrectFileContent,
        string $expectedClassName
    ): void {
        $newContent = str_replace('class ' . $incorrectClassName, 'class ' . $expectedClassName, $incorrectFileContent);
        $this->smartFileSystem->dumpFile((string) $incorrectClassNameFile, $newContent);
    }



    private function getExpectedNamespace(string $path, string $relativePath): ?string
    {
        $relativePath = str_replace('/', '\\', dirname($relativePath, PATHINFO_DIRNAME));
        foreach ($this->psr4autoloadPaths as $prefix => $psr4autoloadPath) {
            if (is_string($psr4autoloadPath) && $psr4autoloadPath === $path) {
                return $prefix . $relativePath;
            }
        }

        return null;
    }

    /**
     * @param array<int, array<int, string>> $matchAll
     */
    private function isFoundCorrectNamespace(array $matchAll, string $expectedNamespace): bool
    {
        if ($matchAll === []) {
            return true;
        }

        $countMatchAll = count($matchAll);
        if ($countMatchAll === 1 && $matchAll[0][1] === $expectedNamespace) {
            return true;
        }

        return $countMatchAll === 2 && $matchAll[0][1] === $expectedNamespace && $matchAll[1][1] === $expectedNamespace;
    }

    /**
     * @param array<int, array<int, string>> $matchAll
     */
    private function getClassName(array $matchAll): string
    {
        $countMatchAll = count($matchAll);

        return $matchAll[0][2];
    }
}
