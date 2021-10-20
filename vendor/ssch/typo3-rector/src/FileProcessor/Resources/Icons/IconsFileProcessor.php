<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Icons;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Feature-77349-AdditionalLocationsForExtensionIcons.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Resources\Icons\IconsProcessor\IconsProcessorTest
 */
final class IconsFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface[]
     */
    private $iconsRector;
    /**
     * @param IconRectorInterface[] $iconsRector
     */
    public function __construct(\Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder, \RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, array $iconsRector)
    {
        $this->filesFinder = $filesFinder;
        $this->smartFileSystem = $smartFileSystem;
        $this->iconsRector = $iconsRector;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        foreach ($this->iconsRector as $iconRector) {
            $iconRector->refactorFile($file);
        }
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if (\strpos($smartFileInfo->getFilename(), 'ext_icon') === \false) {
            return \false;
        }
        $extEmConfSmartFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($smartFileInfo);
        if (!$extEmConfSmartFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return \false;
        }
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return \true;
        }
        return !$this->smartFileSystem->exists($this->createIconPath($file));
    }
    public function getSupportedFileExtensions() : array
    {
        return ['png', 'gif', 'svg'];
    }
    private function createIconPath(\Rector\Core\ValueObject\Application\File $file) : string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPathDirectory();
        $relativeTargetFilePath = \sprintf('/Resources/Public/Icons/Extension.%s', $smartFileInfo->getExtension());
        return $realPath . $relativeTargetFilePath;
    }
}
