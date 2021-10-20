<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Core;

if (\class_exists('TYPO3\\CMS\\Core\\Core\\ClassLoadingInformation')) {
    return;
}
class ClassLoadingInformation
{
    public static function setClassLoader($classLoader)
    {
    }
    /**
     * @return bool
     */
    public static function isClassLoadingInformationAvailable()
    {
        return \true;
    }
    public static function registerClassLoadingInformation()
    {
    }
}
