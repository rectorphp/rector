<?php

namespace RectorPrefix20211031\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\CsvUtility')) {
    return;
}
class CsvUtility
{
    /**
     * @return void
     * @param mixed[] $row
     */
    public static function csvValues($row, $delim = ',', $quote = '"')
    {
    }
}
