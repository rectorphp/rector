<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Icons;

use RectorPrefix20210603\Nette\Utils\Strings;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use Ssch\TYPO3Rector\Helper\FilesFinder;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Feature-77349-AdditionalLocationsForExtensionIcons.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Resources\Icons\IconsProcessor\IconsProcessorTest
 */
final class IconsProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    /**
     * @var mixed[]
     */
    private $iconsRector;
    /**
     * @param IconRectorInterface[] $iconsRector
     */
    public function __construct(\Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder, array $iconsRector)
    {
        $this->filesFinder = $filesFinder;
        $this->iconsRector = $iconsRector;
    }
    /**
     * @param File[] $files
     */
    public function process(array $files) : void
    {
        foreach ($files as $file) {
            foreach ($this->iconsRector as $iconRector) {
                $iconRector->refactorFile($file);
            }
        }
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if (!\RectorPrefix20210603\Nette\Utils\Strings::contains($smartFileInfo->getFilename(), 'ext_icon')) {
            return \false;
        }
        $extEmConfSmartFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($smartFileInfo);
        if (null === $extEmConfSmartFileInfo) {
            return \false;
        }
        return !\file_exists($this->createIconPath($file));
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
