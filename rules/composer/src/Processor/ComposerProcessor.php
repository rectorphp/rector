<?php

declare(strict_types=1);

namespace Rector\Composer\Processor;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Composer\Rector\ComposerModifier;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Process\Process;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\Composer\Tests\Processor\ComposerProcessorTest
 */
final class ComposerProcessor
{
    /**
     * @var ComposerJsonFactory
     */
    private $composerJsonFactory;

    /**
     * @var ComposerJsonPrinter
     */
    private $composerJsonPrinter;

    /**
     * @var ComposerModifier
     */
    private $composerModifier;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    public function __construct(
        ComposerJsonFactory $composerJsonFactory,
        ComposerJsonPrinter $composerJsonPrinter,
        ComposerModifier $composerModifier,
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        ErrorAndDiffCollector $errorAndDiffCollector
    ) {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->composerModifier = $composerModifier;
        $this->configuration = $configuration;
        $this->smartFileSystem = $smartFileSystem;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
    }

    public function process(): void
    {
        $composerJsonFilePath = $this->composerModifier->getFilePath();
        if (! $this->smartFileSystem->exists($composerJsonFilePath)) {
            return;
        }

        $smartFileInfo = new SmartFileInfo($composerJsonFilePath);
        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);

        $oldContents = $smartFileInfo->getContents();
        $this->composerModifier->modify($composerJson);
        $newContents = $this->composerJsonPrinter->printToString($composerJson);

        // nothing has changed
        if ($oldContents === $newContents) {
            return;
        }

        $this->errorAndDiffCollector->addFileDiff($smartFileInfo, $newContents, $oldContents);
        $this->reportFileContentChange($smartFileInfo, $newContents);
    }

    private function reportFileContentChange(SmartFileInfo $smartFileInfo, string $newContent): void
    {
        if ($this->configuration->isDryRun()) {
            return;
        }

        $this->smartFileSystem->dumpFile($smartFileInfo->getRealPath(), $newContent);
        $this->smartFileSystem->chmod($smartFileInfo->getRealPath(), $smartFileInfo->getPerms());

        $command = $this->composerModifier->getCommand();
        $process = new Process(explode(' ', $command), getcwd());
        $process->run(function (string $type, string $message): void {
            // $type is always err https://github.com/composer/composer/issues/3795#issuecomment-76401013
            echo $message;
        });
    }
}
