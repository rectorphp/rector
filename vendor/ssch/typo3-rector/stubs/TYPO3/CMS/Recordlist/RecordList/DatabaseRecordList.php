<?php

namespace RectorPrefix20211020\TYPO3\CMS\Recordlist\RecordList;

if (\class_exists('TYPO3\\CMS\\Recordlist\\RecordList\\DatabaseRecordList')) {
    return;
}
class DatabaseRecordList
{
    /**
     * @return string
     */
    public function thumbCode($row, $table, $field)
    {
        return '';
    }
    /**
     * @return string
     */
    public function requestUri()
    {
        return '';
    }
    /**
     * @return string
     */
    public function listURL()
    {
        return '';
    }
}
