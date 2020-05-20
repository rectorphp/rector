<?php

declare(strict_types=1);

namespace Rector\Compiler\Console\Command;

use OndraM\CiDetector\CiDetector;
use Rector\Compiler\Composer\ComposerJsonManipulator;
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
     * @var CiDetector
     */
    private $ciDetector;

    public function __construct(
        string $dataDir,
        string $buildDir,
        ComposerJsonManipulator $composerJsonManipulator,
        SymfonyStyle $symfonyStyle,
        JetbrainsStubsRenamer $jetbrainsStubsRenamer,
        CiDetector $ciDetector
    ) {
        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;

        $this->composerJsonManipulator = $composerJsonManipulator;
        $this->jetbrainsStubsRenamer = $jetbrainsStubsRenamer;
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
        $composerJsonFile = $this->buildDir . '/composer.json';

        $this->symfonyStyle->title('1. Adding "phpstan/phpstan-src" to ' . $composerJsonFile);
        $this->composerJsonManipulator->fixComposerJson($composerJsonFile);

        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->title('2. Running "composer update" without dev');

        $process = new Process([
            'composer',
            'update',
            '--no-dev',
            '--prefer-dist',
            '--no-interaction',
            '--classmap-authoritative',
            '--ansi',
        ], $this->buildDir, null, null, null);

        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->title('3. Renaming PHPStorm stubs from "*.php" to ".stub"');

        $this->jetbrainsStubsRenamer->renamePhpStormStubs($this->buildDir);

        $this->symfonyStyle->newLine(2);

        // the '--no-parallel' is needed, so "scoper.php.inc" can "require __DIR__ ./vendor/autoload.php"
        // and "Nette\Neon\Neon" class can be used there
        $this->symfonyStyle->title('4. Packing and prefixing rector.phar with Box and PHP Scoper');

        $process = new Process(['php', 'box.phar', 'compile', '--no-parallel'], $this->dataDir, null, null, null);

        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->title('5. Restoring root composer.json with "require-dev"');
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
