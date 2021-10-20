<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Information;

if (\class_exists('TYPO3\\CMS\\Core\\Information\\Typo3Information')) {
    return;
}
class Typo3Information
{
    /**
     * @return string
     */
    public function getCopyrightNotice()
    {
        return 'notice';
    }
}
