<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Icons\Rector;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class IconsRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface
{
    /**
     * @var \Rector\Core\Configuration\Configuration
     */
    private $configuration;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(\Rector\Core\Configuration\Configuration $configuration, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->configuration = $configuration;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function refactorFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $newFullPath = $this->createIconPath($file);
        $this->removedAndAddedFilesCollector->addAddedFile(new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($newFullPath, $smartFileInfo->getContents()));
        $this->createDeepDirectory($newFullPath);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Copy ext_icon.* to Resources/Icons/Extension.*', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
ext_icon.gif
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Resources/Icons/Extension.gif
CODE_SAMPLE
)]);
    }
    private function createDeepDirectory(string $newFullPath) : void
    {
        if ($this->configuration->isDryRun()) {
            return;
        }
        $iconsDirectory = \dirname($newFullPath);
        if (!\is_dir($iconsDirectory)) {
            \mkdir($iconsDirectory, 0777, \true);
        }
    }
    private function createIconPath(\Rector\Core\ValueObject\Application\File $file) : string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPathDirectory();
        $relativeTargetFilePath = \sprintf('/Resources/Public/Icons/Extension.%s', $smartFileInfo->getExtension());
        return $realPath . $relativeTargetFilePath;
    }
}
