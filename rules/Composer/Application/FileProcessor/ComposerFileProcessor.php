<?php

declare (strict_types=1);
namespace Rector\Composer\Application\FileProcessor;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20211020\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix20211020\Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ComposerFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Symplify\ComposerJsonManipulator\ComposerJsonFactory
     */
    private $composerJsonFactory;
    /**
     * @var \Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter
     */
    private $composerJsonPrinter;
    /**
     * @var \Rector\Composer\Contract\Rector\ComposerRectorInterface[]
     */
    private $composerRectors;
    /**
     * @param ComposerRectorInterface[] $composerRectors
     */
    public function __construct(\RectorPrefix20211020\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory, \RectorPrefix20211020\Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter $composerJsonPrinter, array $composerRectors)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->composerRectors = $composerRectors;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
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
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->isJsonInTests($smartFileInfo)) {
            return \true;
        }
        return $smartFileInfo->getBasename() === 'composer.json';
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['json'];
    }
    private function isJsonInTests(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : bool
    {
        if (!\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return \false;
        }
        return $fileInfo->hasSuffixes(['json']);
    }
}
