<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\Controller;

if (\class_exists('TYPO3\\CMS\\Frontend\\Controller\\ErrorController')) {
    return;
}
class ErrorController
{
    /**
     * @return void
     * @param string $message
     * @param mixed[] $reasons
     */
    public function unavailableAction($request, $message, $reasons = [])
    {
    }
    /**
     * @return void
     * @param string $message
     * @param mixed[] $reasons
     */
    public function pageNotFoundAction($request, $message, $reasons = [])
    {
    }
    /**
     * @return void
     * @param string $message
     * @param mixed[] $reasons
     */
    public function accessDeniedAction($request, $message, $reasons = [])
    {
    }
}
