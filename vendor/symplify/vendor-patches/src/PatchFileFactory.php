<?php

declare (strict_types=1);
namespace RectorPrefix20220105\Symplify\VendorPatches;

use RectorPrefix20220105\Nette\Utils\Strings;
use RectorPrefix20220105\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
final class PatchFileFactory
{
    public function createPatchFilePath(\RectorPrefix20220105\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo $oldAndNewFileInfo, string $vendorDirectory) : string
    {
        $newFileInfo = $oldAndNewFileInfo->getNewFileInfo();
        $inVendorRelativeFilePath = $newFileInfo->getRelativeFilePathFromDirectory($vendorDirectory);
        $relativeFilePathWithoutSuffix = \RectorPrefix20220105\Nette\Utils\Strings::lower($inVendorRelativeFilePath);
        $pathFileName = \RectorPrefix20220105\Nette\Utils\Strings::webalize($relativeFilePathWithoutSuffix) . '.patch';
        return 'patches' . \DIRECTORY_SEPARATOR . $pathFileName;
    }
}
