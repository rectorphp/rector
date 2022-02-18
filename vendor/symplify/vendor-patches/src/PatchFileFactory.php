<?php

declare (strict_types=1);
namespace RectorPrefix20220218\Symplify\VendorPatches;

use RectorPrefix20220218\Nette\Utils\Strings;
use RectorPrefix20220218\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
final class PatchFileFactory
{
    public function createPatchFilePath(\RectorPrefix20220218\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo $oldAndNewFileInfo, string $vendorDirectory) : string
    {
        $newFileInfo = $oldAndNewFileInfo->getNewFileInfo();
        $inVendorRelativeFilePath = $newFileInfo->getRelativeFilePathFromDirectory($vendorDirectory);
        $relativeFilePathWithoutSuffix = \RectorPrefix20220218\Nette\Utils\Strings::lower($inVendorRelativeFilePath);
        $pathFileName = \RectorPrefix20220218\Nette\Utils\Strings::webalize($relativeFilePathWithoutSuffix) . '.patch';
        return 'patches' . \DIRECTORY_SEPARATOR . $pathFileName;
    }
}
