<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\Utils\ProjectValidator\Finder\FixtureFinder;
use Rector\Utils\ProjectValidator\Naming\ExpectedNameResolver;
use Rector\Utils\ProjectValidator\Naming\NamespaceMatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ValidateFixtureNamespaceCommand extends Command
{
    /**
     * @var string
     * @see https://regex101.com/r/5KtBi8/2
     */
    private const NAMESPACE_REGEX = '#^namespace (.*);$#msU';

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
    private $namespaceMatcher;

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(
        FinderSanitizer $finderSanitizer,
        SymfonyStyle $symfonyStyle,
        SmartFileSystem $smartFileSystem,
        FixtureFinder $fixtureFinder,
        NamespaceMatcher $namespaceMatcher,
        ExpectedNameResolver $expectedNameResolver
    ) {
        $this->finderSanitizer = $finderSanitizer;
        $this->symfonyStyle = $symfonyStyle;
        $this->currentDirectory = getcwd();
        $this->smartFileSystem = $smartFileSystem;
        $this->fixtureFinder = $fixtureFinder;

        parent::__construct();

        $this->namespaceMatcher = $namespaceMatcher;
        $this->expectedNameResolver = $expectedNameResolver;
    }

    protected function configure(): void
    {
        $this->addOption(Option::FIX, null, null, 'Fix found violations.');
        $this->setDescription('[CI] Validate tests fixtures namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionFix = $input->getOption(Option::FIX);

        $fixtureFileInfos = $this->fixtureFinder->findFixtureFileInfos();
        $incorrectNamespaceFiles = [];

        foreach ($fixtureFileInfos as $fixtureFile) {
            // 1. geting expected namespace ...
            $paths = explode('/tests/', (string) $fixtureFile);
            if (count($paths) > 2) {
                continue;
            }

            $path = ltrim(substr($paths[0], strlen($this->currentDirectory)) . '/tests', '/');
            $expectedNamespace = $this->expectedNameResolver->resolve($path, $paths[1]);

            if ($expectedNamespace === null) {
                continue;
            }

            // 2. reading file contents
            $fileContent = $this->smartFileSystem->readFile((string) $fixtureFile);
            $matchAll = Strings::matchAll($fileContent, self::NAMESPACE_REGEX);

            if ($this->namespaceMatcher->isFoundCorrectNamespace($matchAll, $expectedNamespace)) {
                continue;
            }

            // 3. collect files with incorrect namespace
            $incorrectNamespaceFiles[] = (string) $fixtureFile;
            $incorrectNamespace = $this->getIncorrectNamespace($matchAll, $expectedNamespace);

            if ($optionFix) {
                $this->fixNamespace((string) $fixtureFile, $incorrectNamespace, $fileContent, $expectedNamespace);
            }
        }

        if ($incorrectNamespaceFiles !== []) {
            $this->symfonyStyle->listing($incorrectNamespaceFiles);

            $message = sprintf(
                'Found %d fixture files with invalid namespace which not follow psr-4 defined in composer.json',
                count($incorrectNamespaceFiles)
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

    private function fixNamespace(
        string $incorrectNamespaceFile,
        string $incorrectNamespace,
        string $incorrectFileContent,
        string $expectedNamespace
    ): void {
        $newContent = str_replace(
            'namespace ' . $incorrectNamespace,
            'namespace ' . $expectedNamespace,
            $incorrectFileContent
        );
        $this->smartFileSystem->dumpFile((string) $incorrectNamespaceFile, $newContent);
    }

    /**
     * @param array<int, array<int, string>> $matchAll
     */
    private function getIncorrectNamespace(array $matchAll, string $expectedNamespace): string
    {
        $countMatchAll = count($matchAll);

        if ($countMatchAll === 1) {
            return $matchAll[0][1];
        }

        return $matchAll[0][1] !== $expectedNamespace
            ? $matchAll[0][1]
            : $matchAll[1][1];
    }
}
