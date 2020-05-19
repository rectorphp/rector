<?php

declare(strict_types=1);

namespace Rector\Compiler\Console\Command;

use OndraM\CiDetector\CiDetector;
use Rector\Compiler\Composer\ComposerJsonManipulator;
use Rector\Compiler\Debug\FileLister;
use Rector\Compiler\Renaming\JetbrainsStubsRenamer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\ShellCode;

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

    /**
     * @var JetbrainsStubsRenamer
     */
    private $jetbrainsStubsRenamer;

    /**
     * @var FileLister
     */
    private $fileLister;

    /**
     * @var CiDetector
     */
    private $ciDetector;

    public function __construct(
        string $dataDir,
        string $buildDir,
        ComposerJsonManipulator $composerJsonManipulator,
        SymfonyStyle $symfonyStyle,
        JetbrainsStubsRenamer $jetbrainsStubsRenamer,
        FileLister $fileLister,
        CiDetector $ciDetector
    ) {
        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;

        $this->composerJsonManipulator = $composerJsonManipulator;
        $this->jetbrainsStubsRenamer = $jetbrainsStubsRenamer;
        $this->fileLister = $fileLister;
        $this->symfonyStyle = $symfonyStyle;
        $this->ciDetector = $ciDetector;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::class);
        $this->setDescription('Compile prefixed rector.phar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1.
        $composerJsonFile = $this->buildDir . '/composer.json';
        $this->symfonyStyle->section('Loading and updating ' . $composerJsonFile);
        $this->composerJsonManipulator->fixComposerJson($composerJsonFile);

        // debug
        if (file_exists($this->buildDir . '/vendor/jetbrains')) {
            $this->fileLister->listFilesInDirectory($this->buildDir . '/vendor/jetbrains');
        }

        // 2.
        $this->symfonyStyle->section('Renaming PHPStorm stubs from "*.php" to ".stub"');
        $this->jetbrainsStubsRenamer->renamePhpStormStubs($this->buildDir);

        // 3.
        // the '--no-parallel' is needed, so "scoper.php.inc" can "require __DIR__ ./vendor/autoload.php"
        // and "Nette\Neon\Neon" class can be used there
        $this->symfonyStyle->section('Packing and prefixing rector.phar with Box and PHP Scoper');
        $process = new Process(['php', 'box.phar', 'compile', '--no-parallel'], $this->dataDir, null, null, null);
        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        // 4.
        $this->symfonyStyle->section('Restoring root composer.json with "require-dev"');
        $this->symfonyStyle->note('You still need to run "composer update" to install those dependencies');

        $this->composerJsonManipulator->restoreComposerJson($composerJsonFile);

        $this->restoreDependenciesLocallyIfNotCi($output);

        return ShellCode::SUCCESS;
    }

    private function restoreDependenciesLocallyIfNotCi(OutputInterface $output): void
    {
        if ($this->ciDetector->isCiDetected()) {
            return;
        }

        $process = new Process(['composer', 'install'], $this->buildDir, null, null, null);
        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });
    }
}
