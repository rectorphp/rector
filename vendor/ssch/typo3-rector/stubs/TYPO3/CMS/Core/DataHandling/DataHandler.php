<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\DataHandling;

if (\class_exists('TYPO3\\CMS\\Core\\DataHandling\\DataHandler')) {
    return;
}
class DataHandler
{
    /**
     * @return void
     */
    public function rmComma($input)
    {
    }
    /**
     * @return int
     */
    public function newlog2($message, $table, $uid, $pid = null, $error = 0)
    {
        return 1;
    }
    /**
     * @return mixed[]
     */
    public function getRecordProperties($table, $id, $noWSOL = \false)
    {
        return [];
    }
    /**
     * @return void
     */
    public function log($table, $recuid, $action, $recpid, $error, $details, $details_nr = -1, $data = [], $event_pid = -1, $NEWid = '')
    {
    }
}
