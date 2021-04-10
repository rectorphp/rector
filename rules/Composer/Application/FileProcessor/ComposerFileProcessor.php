<?php

declare(strict_types=1);

namespace Rector\Composer\Application\FileProcessor;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;

final class ComposerFileProcessor implements FileProcessorInterface
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

    public function __construct(
        ComposerJsonFactory $composerJsonFactory,
        ComposerJsonPrinter $composerJsonPrinter,
        ComposerModifier $composerModifier
    ) {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->composerModifier = $composerModifier;
    }

    public function process(File $file): void
    {
        // to avoid modification of file
        if (! $this->composerModifier->enabled()) {
            return;
        }

        $smartFileInfo = $file->getSmartFileInfo();
        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);

        $oldComposerJson = clone $composerJson;
        $this->composerModifier->modify($composerJson);

        // nothing has changed
        if ($oldComposerJson->getJsonArray() === $composerJson->getJsonArray()) {
            return;
        }

        $changeFileContent = $this->composerJsonPrinter->printToString($composerJson);
        $file->changeFileContent($changeFileContent);
    }

    public function supports(File $file): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->getRealPath() === getcwd() . '/composer.json';
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['json'];
    }
}
