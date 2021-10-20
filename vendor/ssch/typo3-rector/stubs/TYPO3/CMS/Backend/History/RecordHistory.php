<?php

namespace RectorPrefix20211020\TYPO3\CMS\Backend\History;

if (\class_exists('TYPO3\\CMS\\Backend\\History\\RecordHistory')) {
    return;
}
class RecordHistory
{
    /**
     * @var array
     */
    public $changeLog;
    /**
     * @var array
     */
    public $lastHistoryEntry;
    /**
     * @return void
     */
    public function getChangeLog()
    {
    }
    /**
     * @return void
     */
    public function getHistoryEntry()
    {
    }
    /**
     * @return void
     */
    public function getHistoryData()
    {
    }
    /**
     * @return void
     */
    public function shouldPerformRollback()
    {
    }
    /**
     * @return void
     */
    public function createChangelog()
    {
    }
    /**
     * @return void
     */
    public function getElementData()
    {
    }
    /**
     * @return void
     */
    public function performRollback()
    {
    }
    /**
     * @return void
     */
    public function createMultipleDiff()
    {
    }
    /**
     * @return void
     */
    public function setLastHistoryEntry()
    {
    }
}
