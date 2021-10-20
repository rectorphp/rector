<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\TimeTracker;

if (\class_exists('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker')) {
    return;
}
class TimeTracker
{
    public function __construct($isEnabled = \true)
    {
    }
    /**
     * @return void
     */
    public function setTSlogMessage($content, $num = 0)
    {
    }
}
