<?php

namespace RectorPrefix20210620\TYPO3\CMS\Core\Log;

if (\class_exists('TYPO3\\CMS\\Core\\Log\\LogManager')) {
    return null;
}
class LogManager
{
    /**
     * @param string $class
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public function getLogger($class)
    {
        $class = (string) $class;
        return new \RectorPrefix20210620\TYPO3\CMS\Core\Log\Logger();
    }
}
