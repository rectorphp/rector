<?php

declare(strict_types=1);

namespace Rector\Compiler\Console;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Compiler\Composer\ComposerJsonManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

/**
 * Inspired by @see https://github.com/phpstan/phpstan-src/blob/f939d23155627b5c2ec6eef36d976dddea22c0c5/compiler/src/Console/CompileCommand.php
 */
final class CompileCommand extends Command
{
    /**
     * @var string
     */
    private $buildDir;

    /**
     * @var string
     */
    private $dataDir;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ComposerJsonManipulator
     */
    private $composerJsonManipulator;

    public function __construct(string $dataDir, string $buildDir, ComposerJsonManipulator $composerJsonManipulator)
    {
        parent::__construct();

        $this->composerJsonManipulator = $composerJsonManipulator;

        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;

        $symfonyStyleFactory = new SymfonyStyleFactory();
        $this->symfonyStyle = $symfonyStyleFactory->create();
    }

    protected function configure(): void
    {
        $this->setName('rector:compile');
        $this->setDescription('Compile prefixed rector.phar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerJsonFile = $this->buildDir . '/composer.json';

        $this->symfonyStyle->note('Loading ' . $composerJsonFile);

        $this->composerJsonManipulator->fixComposerJson($composerJsonFile);

        $this->renamePhpStormStubs();

        // @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/issues/52
        $process = new Process([
            'composer',
            'update',
            '--no-dev',
            '--prefer-dist',
            '--no-interaction',
            '--classmap-authoritative',
        ], $this->buildDir, null, null, null);

        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        // the '--no-parallel' is needed, so "scoper.php.inc" can "require __DIR__ ./vendor/autoload.php"
        // and "Nette\Neon\Neon" class can be used there
        $process = new Process(['php', 'box.phar', 'compile', '--no-parallel'], $this->dataDir, null, null, null);

        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        $this->composerJsonManipulator->restoreComposerJson($composerJsonFile);

        return ShellCode::SUCCESS;
    }

    private function renamePhpStormStubs(): void
    {
        $directory = $this->buildDir . '/vendor/jetbrains/phpstorm-stubs';

        $stubFileInfos = $this->getStubFileInfos($directory);

        foreach ($stubFileInfos as $stubFileInfo) {
            $path = $stubFileInfo->getPathname();

            $filenameWithStubSuffix = dirname($path) . '/' . $stubFileInfo->getBasename('.php') . '.stub';
            FileSystem::rename($path, $filenameWithStubSuffix);
        }

        $this->renameFilesInStubsMap($directory);
    }

    /**
     * @return SplFileInfo[]
     */
    private function getStubFileInfos(string $phpStormStubsDirectory): array
    {
        if (! is_dir($phpStormStubsDirectory)) {
            return [];
        }

        $stubFinder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($phpStormStubsDirectory)
            ->notName('*PhpStormStubsMap.php');

        return iterator_to_array($stubFinder->getIterator());
    }

    private function renameFilesInStubsMap(string $phpStormStubsDirectory): void
    {
        $stubsMapPath = $phpStormStubsDirectory . '/PhpStormStubsMap.php';

        $stubsMapContents = FileSystem::read($stubsMapPath);
        $stubsMapContents = Strings::replace($stubsMapContents, '.php\',', '.stub\',');

        FileSystem::write($stubsMapPath, $stubsMapContents);
    }
}
