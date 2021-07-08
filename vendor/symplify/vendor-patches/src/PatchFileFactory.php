<?php

declare (strict_types=1);
namespace RectorPrefix20210708\Symplify\VendorPatches;

use RectorPrefix20210708\Nette\Utils\Strings;
use RectorPrefix20210708\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
final class PatchFileFactory
{
    public function createPatchFilePath(\RectorPrefix20210708\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo $oldAndNewFileInfo, string $vendorDirectory) : string
    {
        $newFileInfo = $oldAndNewFileInfo->getNewFileInfo();
        $inVendorRelativeFilePath = $newFileInfo->getRelativeFilePathFromDirectory($vendorDirectory);
        $relativeFilePathWithoutSuffix = \RectorPrefix20210708\Nette\Utils\Strings::lower($inVendorRelativeFilePath);
        $pathFileName = \RectorPrefix20210708\Nette\Utils\Strings::webalize($relativeFilePathWithoutSuffix) . '.patch';
        return 'patches' . \DIRECTORY_SEPARATOR . $pathFileName;
    }
}
