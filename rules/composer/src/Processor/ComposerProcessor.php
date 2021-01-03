<?php

declare(strict_types=1);

namespace Rector\Composer\Processor;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Process\Process;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ComposerProcessor
{
    /** @var ComposerModifier */
    private $composerModifier;

    /** @var Configuration */
    private $configuration;

    /** @var SmartFileSystem */
    private $smartFileSystem;

    /** @var ErrorAndDiffCollector */
    private $errorAndDiffCollector;

    public function __construct(
        ComposerModifier $composerModifier,
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        ErrorAndDiffCollector $errorAndDiffCollector
    ) {
        $this->composerModifier = $composerModifier;
        $this->configuration = $configuration;
        $this->smartFileSystem = $smartFileSystem;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
    }

    public function process(): void
    {
        $smartFileInfo = new SmartFileInfo($this->composerModifier->getFilePath());
        $oldContents = $smartFileInfo->getContents();
        $newContents = $this->composerModifier->modify($oldContents);

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
