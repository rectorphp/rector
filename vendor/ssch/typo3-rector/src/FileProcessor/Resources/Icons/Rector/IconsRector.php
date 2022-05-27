<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Icons\Rector;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\Application\File;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use RectorPrefix20220527\Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class IconsRector implements IconRectorInterface
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(ParameterProvider $parameterProvider, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->parameterProvider = $parameterProvider;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function refactorFile(File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $newFullPath = $this->createIconPath($file);
        $this->createDeepDirectory($newFullPath);
        $this->removedAndAddedFilesCollector->addAddedFile(new AddedFileWithContent($newFullPath, $smartFileInfo->getContents()));
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Copy ext_icon.* to Resources/Icons/Extension.*', [new CodeSample(<<<'CODE_SAMPLE'
ext_icon.gif
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Resources/Icons/Extension.gif
CODE_SAMPLE
)]);
    }
    private function createDeepDirectory(string $newFullPath) : void
    {
        if ($this->shouldSkip()) {
            return;
        }
        \mkdir(\dirname($newFullPath), 0777, \true);
    }
    private function createIconPath(File $file) : string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPathDirectory();
        $relativeTargetFilePath = \sprintf('/Resources/Public/Icons/Extension.%s', $smartFileInfo->getExtension());
        return $realPath . $relativeTargetFilePath;
    }
    private function shouldSkip() : bool
    {
        return $this->parameterProvider->provideBoolParameter(Option::DRY_RUN) && !StaticPHPUnitEnvironment::isPHPUnitRun();
    }
}
