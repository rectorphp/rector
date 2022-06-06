<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\Resources\Icons;

use RectorPrefix20220606\Rector\Core\Contract\Processor\FileProcessorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\Error\SystemError;
use RectorPrefix20220606\Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix20220606\Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20220606\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\Resources\IconRectorInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Feature-77349-AdditionalLocationsForExtensionIcons.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Resources\Icons\IconsProcessor\IconsProcessorTest
 */
final class IconsFileProcessor implements FileProcessorInterface
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
     * @var IconRectorInterface[]
     * @readonly
     */
    private $iconsRector;
    /**
     * @param IconRectorInterface[] $iconsRector
     */
    public function __construct(FilesFinder $filesFinder, array $iconsRector)
    {
        $this->filesFinder = $filesFinder;
        $this->iconsRector = $iconsRector;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        foreach ($this->iconsRector as $iconRector) {
            $iconRector->refactorFile($file);
        }
        // to keep parent contract with return values
        return [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->shouldSkip($smartFileInfo->getFilenameWithoutExtension())) {
            return \false;
        }
        $extEmConfSmartFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($smartFileInfo);
        if (!$extEmConfSmartFileInfo instanceof SmartFileInfo) {
            return \false;
        }
        return !\file_exists($this->createIconPath($file));
    }
    public function getSupportedFileExtensions() : array
    {
        return ['png', 'gif', 'svg'];
    }
    private function createIconPath(File $file) : string
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
        return !(StaticPHPUnitEnvironment::isPHPUnitRun() && \strpos($filenameWithoutExtension, self::EXT_ICON_NAME) !== \false);
    }
}
