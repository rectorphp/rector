<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Core\Configuration\ChangeConfiguration;
use Rector\Core\Configuration\Configuration;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

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

    public function __construct(
        Configuration $configuration,
        ChangeConfiguration $changeConfiguration,
        SymfonyStyle $symfonyStyle,
        RenamedClassesCollector $renamedClassesCollector
    ) {
        $this->configuration = $configuration;
        $this->changeConfiguration = $changeConfiguration;
        $this->symfonyStyle = $symfonyStyle;
        $this->renamedClassesCollector = $renamedClassesCollector;
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
        $oldContent = $neonYamlFileInfo->getContents();
        $newContent = $this->renameClasses($oldContent);

        // nothing has changed
        if ($oldContent === $newContent) {
            return $oldContent;
        }

        $this->reportFileContentChange($neonYamlFileInfo, $newContent);

        return $newContent;
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

    private function reportFileContentChange(SmartFileInfo $neonYamlFileInfo, string $newContent): void
    {
        $relativeFilePath = $neonYamlFileInfo->getRelativeFilePathFromCwd();

        if ($this->configuration->isDryRun()) {
            $this->symfonyStyle->note(
                sprintf('File "%s" would be changed ("dry-run" is on now)', $relativeFilePath)
            );
        } else {
            $this->symfonyStyle->note(sprintf('File "%s" was changed', $relativeFilePath));
            FileSystem::write($neonYamlFileInfo->getRealPath(), $newContent, $neonYamlFileInfo->getPerms());
        }
    }
}
