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
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * Inspired by @see https://github.com/phpstan/phpstan-src/blob/f939d23155627b5c2ec6eef36d976dddea22c0c5/compiler/src/Console/CompileCommand.php
 */
final class CompileCommand extends Command
{
    /**
     * @var string
     */
    private const ANSI = '--ansi';

    /**
     * @var string
     */
    private const DONE = 'Done';

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

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        string $dataDir,
        string $buildDir,
        ComposerJsonManipulator $composerJsonManipulator,
        SymfonyStyle $symfonyStyle,
        JetbrainsStubsRenamer $jetbrainsStubsRenamer,
        CiDetector $ciDetector,
        SmartFileSystem $smartFileSystem
    ) {
        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;

        $this->composerJsonManipulator = $composerJsonManipulator;
        $this->jetbrainsStubsRenamer = $jetbrainsStubsRenamer;
        $this->symfonyStyle = $symfonyStyle;
        $this->ciDetector = $ciDetector;
        $this->smartFileSystem = $smartFileSystem;

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

        $title = sprintf('1. Adding "phpstan/phpstan-src" to "%s"', $composerJsonFile);
        $this->symfonyStyle->title($title);

        $this->composerJsonManipulator->fixComposerJson($composerJsonFile);

        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->success(self::DONE);

        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('2. Running "composer update" without dev');

        $process = new Process([
            'composer',
            'update',
            '--no-dev',
            '--prefer-dist',
            '--no-interaction',
            '--classmap-authoritative',
            self::ANSI,
        ], $this->buildDir, null, null, null);
        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        $this->symfonyStyle->success(self::DONE);

        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('3. Downgrading PHPStan code to PHP 7.1');

        $this->downgradePHPStanCodeToPHP71($output);

        $this->symfonyStyle->success(self::DONE);
        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('4. Renaming PHPStorm stubs from "*.php" to ".stub"');

        $this->jetbrainsStubsRenamer->renamePhpStormStubs($this->buildDir);

        $this->symfonyStyle->success(self::DONE);
        $this->symfonyStyle->newLine(1);

        // the '--no-parallel' is needed, so "scoper.php.inc" can "require __DIR__ ./vendor/autoload.php"
        // and "Nette\Neon\Neon" class can be used there
        $this->symfonyStyle->title('5. Packing and prefixing rector.phar with Box and PHP Scoper');

        $process = new Process([
            'php',
            'box.phar',
            'compile',
            '--no-parallel',
            self::ANSI,
        ], $this->dataDir, null, null, null);
        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });

        $this->symfonyStyle->success(self::DONE);

        $this->symfonyStyle->newLine(1);

        $this->symfonyStyle->title('6. Restoring root composer.json with "require-dev"');

        $this->composerJsonManipulator->restoreComposerJson($composerJsonFile);
        $this->restoreDependenciesLocallyIfNotCi($output);

        $this->symfonyStyle->success(self::DONE);

        return ShellCode::SUCCESS;
    }

    private function downgradePHPStanCodeToPHP71(OutputInterface $output): void
    {
        // downgrade phpstan-src code from PHP 7.4 to PHP 7.1, see https://github.com/phpstan/phpstan-src/pull/202/files
        $this->fixRequirePath();

        $process = new Process(['php', 'vendor/phpstan/phpstan-src/bin/transform-source.php'], $this->buildDir);
        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });
    }

    private function restoreDependenciesLocallyIfNotCi(OutputInterface $output): void
    {
        if ($this->ciDetector->isCiDetected()) {
            return;
        }

        $process = new Process(['composer', 'install', self::ANSI], $this->buildDir, null, null, null);
        $process->mustRun(static function (string $type, string $buffer) use ($output): void {
            $output->write($buffer);
        });
    }

    private function fixRequirePath(): void
    {
        // fix require path first
        $filePath = __DIR__ . '/../../../../vendor/phpstan/phpstan-src/bin/transform-source.php';
        $fileContent = $this->smartFileSystem->readFile($filePath);

        $fileContent = str_replace(
            "__DIR__ . '/../vendor/autoload.php'",
            "__DIR__ . '/../../../../vendor/autoload.php'",
            $fileContent
        );

        $this->smartFileSystem->dumpFile($filePath, $fileContent);
    }
}
