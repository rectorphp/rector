<?php

namespace RectorPrefix20210630\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\CsvUtility')) {
    return;
}
class CsvUtility
{
    /**
     * @return void
     */
    public static function csvValues(array $row, $delim = ',', $quote = '"')
    {
    }
}
