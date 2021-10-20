<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility')) {
    return;
}
class ExtensionManagementUtility
{
    /**
     * @return bool
     */
    public static function isLoaded($key, $exitOnError = null)
    {
        return \true;
    }
    public static function siteRelPath($key)
    {
        return $key;
    }
    public static function extPath($key)
    {
        return $key;
    }
    public static function removeCacheFiles()
    {
        return null;
    }
    /**
     * @return string
     */
    public static function findService($serviceType, $serviceSubType = '', $excludeServiceKeys = [])
    {
        return 'foo';
    }
    /**
     * @return mixed[]
     */
    public static function configureModule($moduleSignature, $modulePath)
    {
        return [];
    }
    /**
     * @return void
     * @param bool $false
     */
    public static function loadExtLocalconf($false)
    {
    }
    /**
     * @return void
     * @param string $key
     */
    public static function extRelPath($key)
    {
    }
    /**
     * @return void
     */
    public static function addStaticFile($extKey, $path, $title)
    {
    }
    /**
     * @param string $fieldName
     * @param array $customSettingOverride
     * @param string $allowedFileExtensions
     * @param string $disallowedFileExtensions
     * @return array
     */
    public static function getFileFieldTCAConfig($fieldName, $customSettingOverride = [], $allowedFileExtensions = '', $disallowedFileExtensions = '')
    {
        return [];
    }
    /**
     * @param string $string
     * @param mixed[] $columns
     */
    public static function addTCAcolumns($string, $columns)
    {
    }
}
