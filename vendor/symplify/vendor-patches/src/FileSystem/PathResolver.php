<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\VendorPatches\FileSystem;

use RectorPrefix20211231\Nette\Utils\Strings;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211231\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
final class PathResolver
{
    /**
     * @see https://regex101.com/r/KhzCSu/1
     * @var string
     */
    private const VENDOR_PACKAGE_DIRECTORY_REGEX = '#^(?<vendor_package_directory>.*?vendor\\/(\\w|\\.|\\-)+\\/(\\w|\\.|\\-)+)\\/#si';
    public function resolveVendorDirectory(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : string
    {
        $match = \RectorPrefix20211231\Nette\Utils\Strings::match($fileInfo->getRealPath(), self::VENDOR_PACKAGE_DIRECTORY_REGEX);
        if (!isset($match['vendor_package_directory'])) {
            throw new \RectorPrefix20211231\Symplify\SymplifyKernel\Exception\ShouldNotHappenException('Could not resolve vendor package directory');
        }
        return $match['vendor_package_directory'];
    }
}
