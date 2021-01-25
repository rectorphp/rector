<?php

declare(strict_types=1);

namespace Rector\Composer\Processor;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Process\Process;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ComposerProcessor
{
    /**
     * @var string
     */
    private const COMPOSER_UPDATE = 'composer update';

    /**
     * @var ComposerJsonFactory
     */
    private $composerJsonFactory;

    /**
     * @var ComposerJsonPrinter
     */
    private $composerJsonPrinter;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var ComposerModifier
     */
    private $composerModifier;

    public function __construct(
        ComposerJsonFactory $composerJsonFactory,
        ComposerJsonPrinter $composerJsonPrinter,
        Configuration $configuration,
        ErrorAndDiffCollector $errorAndDiffCollector,
        SmartFileSystem $smartFileSystem,
        ComposerModifier $composerModifier
    ) {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->configuration = $configuration;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->smartFileSystem = $smartFileSystem;
        $this->composerModifier = $composerModifier;
    }

    public function process(string $composerJsonFilePath): void
    {
        if (! $this->smartFileSystem->exists($composerJsonFilePath)) {
            return;
        }

        // to avoid modification of file
        if (! $this->composerModifier->enabled()) {
            return;
        }

        $smartFileInfo = new SmartFileInfo($composerJsonFilePath);
        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);

        $oldComposerJson = clone $composerJson;
        $this->composerModifier->modify($composerJson);

        // nothing has changed
        if ($oldComposerJson->getJsonArray() === $composerJson->getJsonArray()) {
            return;
        }

        $this->addComposerJsonFileDiff($oldComposerJson, $composerJson, $smartFileInfo);
        $this->reportFileContentChange($composerJson, $smartFileInfo);
    }

    private function addComposerJsonFileDiff(
        ComposerJson $oldComposerJson,
        ComposerJson $newComposerJson,
        SmartFileInfo $smartFileInfo
    ): void {
        $newContents = $this->composerJsonPrinter->printToString($newComposerJson);
        $oldContents = $this->composerJsonPrinter->printToString($oldComposerJson);
        $this->errorAndDiffCollector->addFileDiff($smartFileInfo, $newContents, $oldContents);
    }

    private function reportFileContentChange(ComposerJson $composerJson, SmartFileInfo $smartFileInfo): void
    {
        if ($this->configuration->isDryRun()) {
            return;
        }

        $this->composerJsonPrinter->print($composerJson, $smartFileInfo);

        $process = new Process(explode(' ', self::COMPOSER_UPDATE), getcwd());
        $process->run(function (string $type, string $message): void {
            // $type is always err https://github.com/composer/composer/issues/3795#issuecomment-76401013
            echo $message;
        });
    }
}
