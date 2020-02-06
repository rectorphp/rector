<?php

declare(strict_types=1);

namespace Rector\Yaml;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Configuration\ChangeConfiguration;
use Rector\Configuration\Configuration;
use Rector\FileSystem\FilesFinder;
use Symfony\Component\Console\Style\SymfonyStyle;

final class YamlProcessor
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var ChangeConfiguration
     */
    private $changeConfiguration;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        Configuration $configuration,
        FilesFinder $filesFinder,
        ChangeConfiguration $changeConfiguration,
        SymfonyStyle $symfonyStyle
    ) {
        $this->configuration = $configuration;
        $this->filesFinder = $filesFinder;
        $this->changeConfiguration = $changeConfiguration;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function run(): void
    {
        $source = $this->configuration->getSource();
        $yamlFileInfos = $this->filesFinder->findInDirectoriesAndFiles($source, ['yaml']);

        // 1. raw class rename

        $oldToNewClasses = $this->changeConfiguration->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return;
        }

        foreach ($yamlFileInfos as $yamlFileInfo) {
            $oldContent = $yamlFileInfo->getContents();
            $newContent = $oldContent;

            foreach ($oldToNewClasses as $oldClass => $newClass) {
                /** @var string $newContent */
                $newContent = Strings::replace($newContent, '#' . preg_quote($oldClass) . '#', $newClass);
            }

            // nothing has changed
            if ($oldContent === $newContent) {
                continue;
            }

            $relativeFilePath = $yamlFileInfo->getRelativeFilePathFromCwd();

            if ($this->configuration->isDryRun()) {
                $this->symfonyStyle->note(sprintf('File %s would be changed (dry-run is on now)', $relativeFilePath));
                continue;
            }

            $this->symfonyStyle->note(sprintf('File %s was changed', $relativeFilePath));
            FileSystem::write($yamlFileInfo->getRealPath(), $newContent);
        }
    }
}
