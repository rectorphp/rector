<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\RootlineUtility')) {
    return;
}
class RootlineUtility
{
    public function __construct($uid, $mountPointParameter = '', $context = null)
    {
    }
    /**
     * @return mixed[]
     */
    public function get()
    {
        return [];
    }
}
