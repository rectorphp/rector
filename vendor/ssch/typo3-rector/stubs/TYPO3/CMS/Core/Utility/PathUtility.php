<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\PathUtility')) {
    return;
}
class PathUtility
{
    public static function stripPathSitePrefix($path)
    {
        return $path;
    }
    public static function getAbsoluteWebPath($path)
    {
        return $path;
    }
}
