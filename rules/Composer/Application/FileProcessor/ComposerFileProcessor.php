<?php

declare(strict_types=1);

namespace Rector\Composer\Application\FileProcessor;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ComposerFileProcessor implements FileProcessorInterface
{
    /**
     * @param ComposerRectorInterface[] $composerRectors
     */
    public function __construct(
        private ComposerJsonFactory $composerJsonFactory,
        private ComposerJsonPrinter $composerJsonPrinter,
        private array $composerRectors
    ) {
    }

    public function process(File $file, Configuration $configuration): void
    {
        if ($this->composerRectors === []) {
            return;
        }

        // to avoid modification of file
        $smartFileInfo = $file->getSmartFileInfo();
        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);

        $oldComposerJson = clone $composerJson;
        foreach ($this->composerRectors as $composerRector) {
            $composerRector->refactor($composerJson);
        }

        // nothing has changed
        if ($oldComposerJson->getJsonArray() === $composerJson->getJsonArray()) {
            return;
        }

        $changeFileContent = $this->composerJsonPrinter->printToString($composerJson);
        $file->changeFileContent($changeFileContent);
    }

    public function supports(File $file, Configuration $configuration): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        if ($this->isJsonInTests($smartFileInfo)) {
            return true;
        }

        return $smartFileInfo->getBasename() === 'composer.json';
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['json'];
    }

    private function isJsonInTests(SmartFileInfo $fileInfo): bool
    {
        if (! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return false;
        }

        return $fileInfo->hasSuffixes(['json']);
    }
}
