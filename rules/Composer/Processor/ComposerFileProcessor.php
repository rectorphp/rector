<?php

declare(strict_types=1);

namespace Rector\Composer\Processor;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\NonPhpFile\NonPhpFileChange;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;

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

    public function process(SmartFileInfo $smartFileInfo): ?NonPhpFileChange
    {
        // to avoid modification of file
        if (! $this->composerModifier->enabled()) {
            return null;
        }

        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);
        $oldComposerJson = clone $composerJson;
        $this->composerModifier->modify($composerJson);

        // nothing has changed
        if ($oldComposerJson->getJsonArray() === $composerJson->getJsonArray()) {
            return null;
        }

        $oldContent = $this->composerJsonPrinter->printToString($oldComposerJson);
        $newContent = $this->composerJsonPrinter->printToString($composerJson);

        return new NonPhpFileChange($oldContent, $newContent);
    }

    public function supports(SmartFileInfo $smartFileInfo): bool
    {
        return $smartFileInfo->getRealPath() === getcwd() . '/composer.json';
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['json'];
    }
}
