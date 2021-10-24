<?php

namespace RectorPrefix20211024\TYPO3\CMS\Core\Package;

if (\class_exists('TYPO3\\CMS\\Core\\Package\\PackageManager')) {
    return;
}
class PackageManager
{
    /**
     * @return mixed[]
     */
    public function getActivePackages()
    {
        return [];
    }
    /**
     * @return bool
     */
    public function isPackageActive($key)
    {
        return \true;
    }
}
