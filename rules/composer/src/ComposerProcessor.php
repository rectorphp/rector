<?php

declare(strict_types=1);

namespace Rector\Composer;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ComposerProcessor
{
    /** @var ComposerRector */
    private $composerRector;

    /** @var Configuration */
    private $configuration;

    /** @var SymfonyStyle */
    private $symfonyStyle;

    /** @var SmartFileSystem */
    private $smartFileSystem;

    /** @var ErrorAndDiffCollector */
    private $errorAndDiffCollector;

    public function __construct(
        ComposerRector $composerRector,
        Configuration $configuration,
        SymfonyStyle $symfonyStyle,
        SmartFileSystem $smartFileSystem,
        ErrorAndDiffCollector $errorAndDiffCollector
    ) {
        $this->composerRector = $composerRector;
        $this->configuration = $configuration;
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
    }

    public function process(): void
    {
        $smartFileInfo = new SmartFileInfo($this->composerRector->getFilePath());
        $oldContents = $smartFileInfo->getContents();
        $newContents = $this->composerRector->process($oldContents);

        // nothing has changed
        if ($oldContents === $newContents) {
            return;
        }

        $this->errorAndDiffCollector->addFileDiff($smartFileInfo, $newContents, $oldContents);
        $this->reportFileContentChange($smartFileInfo, $newContents);
    }

    private function reportFileContentChange(SmartFileInfo $smartFileInfo, string $newContent): void
    {
        $relativeFilePathFromCwd = $smartFileInfo->getRelativeFilePathFromCwd();

        if ($this->configuration->isDryRun()) {
            $message = sprintf('File "%s" would be changed ("dry-run" is on now)', $relativeFilePathFromCwd);
            $this->symfonyStyle->note($message);
        } else {
            $message = sprintf('File "%s" was changed', $relativeFilePathFromCwd);
            $this->symfonyStyle->note($message);

            $this->smartFileSystem->dumpFile($smartFileInfo->getRealPath(), $newContent);
            $this->smartFileSystem->chmod($smartFileInfo->getRealPath(), $smartFileInfo->getPerms());

            $command = $this->composerRector->getCommand();
            $process = new Process(explode(' ', $command), getcwd());
            $process->run(function (string $type, string $message) {
                // $type is always err https://github.com/composer/composer/issues/3795#issuecomment-76401013
                echo $message;
            });
        }
    }
}
