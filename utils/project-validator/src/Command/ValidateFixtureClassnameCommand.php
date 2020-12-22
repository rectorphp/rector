<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Utils\ProjectValidator\Finder\FixtureFinder;
use Rector\Utils\ProjectValidator\Naming\ExpectedNameResolver;
use Rector\Utils\ProjectValidator\Naming\NamespaceMatcher;
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
        'array',
        'callable',
        'scalar',
        'throw',
        'boolean',
        'elseif',
        'exit',
        'function',
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
    /**
     * @var NamespaceMatcher
     */
    private $namespaceMather;
    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(
        FinderSanitizer $finderSanitizer,
        SymfonyStyle $symfonyStyle,
        ExpectedNameResolver $expectedNameResolver,
        SmartFileSystem $smartFileSystem,
        FixtureFinder $fixtureFinder,
        NamespaceMatcher $namespaceMather
    ) {
        $this->finderSanitizer = $finderSanitizer;
        $this->symfonyStyle = $symfonyStyle;
        $this->currentDirectory = getcwd();
        $this->smartFileSystem = $smartFileSystem;

        parent::__construct();

        $this->fixtureFinder = $fixtureFinder;
        $this->namespaceMather = $namespaceMather;
        $this->expectedNameResolver = $expectedNameResolver;
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

            $expectedNamespace = $this->expectedNameResolver->resolve($path, $paths[1]);
            if ($expectedNamespace === null) {
                continue;
            }

            // 2. reading file contents
            $fileContent = $this->smartFileSystem->readFile((string) $fixtureFileInfo);

            $matchAll = Strings::matchAll($fileContent, self::NAMESPACE_REGEX);

            if (! $this->namespaceMather->isFoundCorrectNamespace($matchAll, $expectedNamespace)) {
                continue;
            }

            $incorrectClassNameFiles = $this->checkAndFixClassName(
                $fileContent,
                $fixtureFileInfo,
                $incorrectClassNameFiles,
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

    /**
     * @param array<int, array<int, string>> $matchAll
     */
    private function getClassName(array $matchAll): string
    {
        return $matchAll[0][2];
    }
}
