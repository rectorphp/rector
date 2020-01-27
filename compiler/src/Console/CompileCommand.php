<?php

declare(strict_types=1);

namespace Rector\Compiler\Console;

use Nette\Utils\FileSystem as NetteFileSystem;
use Nette\Utils\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Inspired by @see https://github.com/phpstan/phpstan-src/blob/f939d23155627b5c2ec6eef36d976dddea22c0c5/compiler/src/Console/CompileCommand.php
 */
final class CompileCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $dataDir;

    /**
     * @var string
     */
    private $buildDir;

    /**
     * @var string
     */
    private $originalComposerJsonFileContent;

    public function __construct(string $dataDir, string $buildDir)
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;
    }

    protected function configure(): void
    {
        $this->setName('rector:compile');
        $this->setDescription('Compile prefixed rector.phar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerJsonFile = $this->buildDir . '/composer.json';

        $this->fixComposerJson($composerJsonFile);

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

        $this->restoreComposerJson($composerJsonFile);

        return 0;
    }

    private function fixComposerJson(string $composerJsonFile): void
    {
        $fileContent = NetteFileSystem::read($composerJsonFile);
        $this->originalComposerJsonFileContent = $fileContent;

        $json = Json::decode($fileContent, Json::FORCE_ARRAY);

        $json = $this->removeDevKeys($json);

        $json = $this->replacePHPStanWithPHPStanSrc($json);
        $json = $this->addReplace($json);

        $encodedJson = Json::encode($json, Json::PRETTY);

        $this->filesystem->dumpFile($composerJsonFile, $encodedJson);
    }

    /**
     * This prevent root composer.json constant override
     */
    private function restoreComposerJson(string $composerJsonFile): void
    {
        $this->filesystem->dumpFile($composerJsonFile, $this->originalComposerJsonFileContent);

        // re-run @todo composer update on root
    }

    /**
     * Use phpstan/phpstan-src, because the phpstan.phar cannot be packed into rector.phar
     */
    private function replacePHPStanWithPHPStanSrc(array $json): array
    {
        $phpstanVersion = $json['require']['phpstan/phpstan'];
        $json['require']['phpstan/phpstan-src'] = $phpstanVersion;
        unset($json['require']['phpstan/phpstan']);

        $json['repositories'][] = [
            'type' => 'vcs',
            'url' => 'https://github.com/phpstan/phpstan-src.git',
        ];

        return $json;
    }

    private function removeDevKeys(array $json): array
    {
        $keysToRemove = ['require-dev', 'autoload-dev', 'replace'];

        foreach ($keysToRemove as $keyToRemove) {
            unset($json[$keyToRemove]);
        }

        return $json;
    }

    /**
     * This prevent installing packages, that are not needed here.
     */
    private function addReplace(array $json): array
    {
        $json['replace'] = [
            'symfony/var-dumper' => '*',
        ];

        return $json;
    }
}
