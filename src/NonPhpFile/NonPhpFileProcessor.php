<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Nette\Utils\Strings;
use Rector\Core\Configuration\ChangeConfiguration;
use Rector\Core\Configuration\Configuration;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

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

    public function __construct(
        ChangeConfiguration $changeConfiguration,
        Configuration $configuration,
        RenamedClassesCollector $renamedClassesCollector,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
        $this->configuration = $configuration;
        $this->changeConfiguration = $changeConfiguration;
        $this->symfonyStyle = $symfonyStyle;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->smartFileSystem = $smartFileSystem;
    }

    /**
     * @param SmartFileInfo[] $neonYamlFileInfos
     */
    public function runOnFileInfos(array $neonYamlFileInfos): void
    {
        foreach ($neonYamlFileInfos as $neonYamlFileInfo) {
            $this->processFileInfo($neonYamlFileInfo);
        }
    }

    public function processFileInfo(SmartFileInfo $neonYamlFileInfo): string
    {
        $oldContents = $neonYamlFileInfo->getContents();
        $newContents = $this->renameClasses($oldContents);

        // nothing has changed
        if ($oldContents === $newContents) {
            return $oldContents;
        }

        $this->reportFileContentChange($neonYamlFileInfo, $newContents);

        return $newContents;
    }

    private function renameClasses(string $newContent): string
    {
        foreach ($this->getOldToNewClasses() as $oldClass => $newClass) {
            /** @var string $newContent */
            $newContent = Strings::replace($newContent, '#' . preg_quote($oldClass, '#') . '#', $newClass);
        }

        // process with double quotes too, e.g. in twig
        foreach ($this->getOldToNewClasses() as $oldClass => $newClass) {
            $doubleSlashOldClass = str_replace('\\', '\\\\', $oldClass);
            $doubleSlashNewClass = str_replace('\\', '\\\\\\', $newClass);

            /** @var string $newContent */
            $newContent = Strings::replace(
                $newContent,
                '#' . preg_quote($doubleSlashOldClass, '#') . '#',
                $doubleSlashNewClass
            );
        }

        return $newContent;
    }

    private function reportFileContentChange(SmartFileInfo $neonYamlFileInfo, string $newContent): void
    {
        $relativeFilePathFromCwd = $neonYamlFileInfo->getRelativeFilePathFromCwd();

        if ($this->configuration->isDryRun()) {
            $message = sprintf('File "%s" would be changed ("dry-run" is on now)', $relativeFilePathFromCwd);
            $this->symfonyStyle->note($message);
        } else {
            $message = sprintf('File "%s" was changed', $relativeFilePathFromCwd);
            $this->symfonyStyle->note($message);

            $this->smartFileSystem->dumpFile($neonYamlFileInfo->getRealPath(), $newContent);
            $this->smartFileSystem->chmod($neonYamlFileInfo->getRealPath(), $neonYamlFileInfo->getPerms());
        }
    }

    /**
     * @return string[]
     */
    private function getOldToNewClasses(): array
    {
        return array_merge(
            $this->changeConfiguration->getOldToNewClasses(),
            $this->renamedClassesCollector->getOldToNewClasses()
        );
    }
}
