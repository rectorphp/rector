<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Configuration\ChangeConfiguration;
use Rector\Core\Configuration\Configuration;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\Renaming\Tests\Rector\Name\RenameClassRector\RenameNonPhpTest
 */
final class NonPhpFileProcessor
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ChangeConfiguration
     */
    private $changeConfiguration;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var NonPhpFileClassRenamer
     */
    private $nonPhpFileClassRenamer;

    public function __construct(
        ChangeConfiguration $changeConfiguration,
        Configuration $configuration,
        RenamedClassesCollector $renamedClassesCollector,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle,
        NonPhpFileClassRenamer $nonPhpFileClassRenamer
    ) {
        $this->configuration = $configuration;
        $this->changeConfiguration = $changeConfiguration;
        $this->symfonyStyle = $symfonyStyle;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->smartFileSystem = $smartFileSystem;
        $this->nonPhpFileClassRenamer = $nonPhpFileClassRenamer;
    }

    /**
     * @param SmartFileInfo[] $nonPhpFileInfos
     */
    public function runOnFileInfos(array $nonPhpFileInfos): void
    {
        foreach ($nonPhpFileInfos as $nonPhpFileInfo) {
            $this->processFileInfo($nonPhpFileInfo);
        }
    }

    public function processFileInfo(SmartFileInfo $smartFileInfo): string
    {
        $oldContents = $smartFileInfo->getContents();

        $classRenames = array_merge(
            $this->changeConfiguration->getOldToNewClasses(),
            $this->renamedClassesCollector->getOldToNewClasses()
        );

        $newContents = $this->nonPhpFileClassRenamer->renameClasses($oldContents, $classRenames);

        // nothing has changed
        if ($oldContents === $newContents) {
            return $oldContents;
        }

        $this->reportFileContentChange($smartFileInfo, $newContents);

        return $newContents;
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
        }
    }
}
