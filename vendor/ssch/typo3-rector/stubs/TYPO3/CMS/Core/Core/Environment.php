<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Core;

if (\class_exists('TYPO3\\CMS\\Core\\Core\\Environment')) {
    return;
}
class Environment
{
    /**
     * @return bool
     */
    public static function isCli()
    {
        return \false;
    }
    /**
     * @return string
     */
    public static function getProjectPath()
    {
        return '';
    }
    /**
     * @return bool
     */
    public static function isRunningOnCgiServer()
    {
        return \false;
    }
    /**
     * @return string
     */
    public static function getContext()
    {
        return 'foo';
    }
}
