<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Information;

if (\class_exists('TYPO3\\CMS\\Core\\Information\\Typo3Version')) {
    return;
}
class Typo3Version
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '9.5.21';
    }
    /**
     * @return string
     */
    public function getBranch()
    {
        return '9.5';
    }
}
