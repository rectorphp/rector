<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Controller;

if (\class_exists('TYPO3\\CMS\\Core\\Controller\\ErrorPageController')) {
    return;
}
class ErrorPageController
{
    /**
     * @param string $title
     * @param string $message
     * @param int $severity
     * @param int $errorCode
     * @return string
     */
    public function errorAction($title, $message, $severity = 0, $errorCode = 0)
    {
    }
}
