<?php

declare(strict_types=1);

namespace Rector\Compiler\Console;

use Nette\Utils\FileSystem as NetteFileSystem;
use Nette\Utils\Json;
use Rector\Compiler\Process\ProcessFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Inspired by @see https://github.com/phpstan/phpstan-src/blob/f939d23155627b5c2ec6eef36d976dddea22c0c5/compiler/src/Console/CompileCommand.php
 */
final class CompileCommand extends Command
{
    /**
     * @var string
     */
    public const NAME = 'rector:compile';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

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

    public function __construct(ProcessFactory $processFactory, string $dataDir, string $buildDir)
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->processFactory = $processFactory;
        $this->dataDir = $dataDir;
        $this->buildDir = $buildDir;
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription('Compile prefixed rector.phar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processFactory->setOutput($output);

        // this breaks phpstan dependency by removing whole "/conf" directory - https://github.com/dg/composer-cleaner#configuration
        $this->processFactory->create(['composer', 'require', '--no-update', 'dg/composer-cleaner:^2.0'], $this->buildDir);

        $composerJsonFile = $this->buildDir . '/composer.json';

        $this->fixComposerJson($composerJsonFile);
        $this->processFactory->create(['composer', 'update', '--no-dev', '--classmap-authoritative'], $this->buildDir);
        $this->processFactory->create(['php', 'box.phar', 'compile'], $this->dataDir);

        $this->restoreComposerJson($composerJsonFile);

        return 0;
    }

    private function fixComposerJson(string $composerJsonFile): void
    {
        $fileContent = NetteFileSystem::read($composerJsonFile);
        $this->originalComposerJsonFileContent = $fileContent;

        $json = Json::decode($fileContent, Json::FORCE_ARRAY);

        // remove dev dependencies (they create conflicts)
        unset($json['require-dev'], $json['autoload-dev']);

        unset($json['replace']);

        $json['name'] = 'rector/rector';

        // simplify autoload (remove not packed build directory]
        $json['autoload']['psr-4']['Rector\\'] = 'src';

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
}
