<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Database;

if (\class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
    return;
}
class ConnectionPool
{
    /**
     * @param string $table
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    public function getConnectionForTable($table)
    {
        $table = (string) $table;
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Database\Connection();
    }
    /**
     * @return void
     */
    public function getQueryBuilderForTable($table)
    {
    }
}
