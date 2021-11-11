<?php

namespace RectorPrefix20211111\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\MailUtility')) {
    return;
}
class MailUtility
{
    /**
     * @return mixed[]
     */
    public static function parseAddresses($rawAddresses)
    {
        return [];
    }
}
