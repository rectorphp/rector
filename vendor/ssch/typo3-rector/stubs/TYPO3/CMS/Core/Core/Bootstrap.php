<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Core;

use RectorPrefix20211020\TYPO3\CMS\Core\Utility\GeneralUtility;
if (\class_exists('TYPO3\\CMS\\Core\\Core\\Bootstrap')) {
    return;
}
class Bootstrap
{
    /**
     * @var Bootstrap
     */
    private static $instance;
    /**
     * @return $this
     */
    public static function getInstance()
    {
        self::$instance = new self();
        return self::$instance;
    }
    /**
     * @return void
     */
    public static function usesComposerClassLoading()
    {
    }
    /**
     * @return $this
     */
    public static function initializeClassLoader($classLoader)
    {
        return self::$instance;
    }
    /**
     * @return $this
     */
    public function ensureClassLoadingInformationExists()
    {
        return self::$instance;
    }
    /**
     * @return $this
     * @param int $requestType
     */
    public function setRequestType($requestType = 0)
    {
        $requestType = (int) $requestType;
        return self::$instance;
    }
    /**
     * @return void
     */
    public function baseSetup()
    {
    }
}
