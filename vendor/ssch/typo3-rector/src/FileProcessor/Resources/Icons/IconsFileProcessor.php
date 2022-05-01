<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Icons;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220501\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Feature-77349-AdditionalLocationsForExtensionIcons.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Resources\Icons\IconsProcessor\IconsProcessorTest
 */
final class IconsFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var string
     */
    private const EXT_ICON_NAME = 'ext_icon';
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var IconRectorInterface[]
     * @readonly
     */
    private $iconsRector;
    /**
     * @param IconRectorInterface[] $iconsRector
     */
    public function __construct(\Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder, \RectorPrefix20220501\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, array $iconsRector)
    {
        $this->filesFinder = $filesFinder;
        $this->smartFileSystem = $smartFileSystem;
        $this->iconsRector = $iconsRector;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        foreach ($this->iconsRector as $iconRector) {
            $iconRector->refactorFile($file);
        }
        // to keep parent contract with return values
        return [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->shouldSkip($smartFileInfo->getFilenameWithoutExtension())) {
            return \false;
        }
        $extEmConfSmartFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($smartFileInfo);
        if (!$extEmConfSmartFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return \false;
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
    private function shouldSkip(string $filenameWithoutExtension) : bool
    {
        if (self::EXT_ICON_NAME === $filenameWithoutExtension) {
            return \false;
        }
        return !(\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun() && \strpos($filenameWithoutExtension, self::EXT_ICON_NAME) !== \false);
    }
}
