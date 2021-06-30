<?php

namespace RectorPrefix20210630\TYPO3\CMS\Frontend\Controller;

if (\class_exists('TYPO3\\CMS\\Frontend\\Controller\\ErrorController')) {
    return;
}
class ErrorController
{
    /**
     * @return void
     * @param string $message
     */
    public function unavailableAction($request, $message, array $reasons = [])
    {
    }
    /**
     * @return void
     * @param string $message
     */
    public function pageNotFoundAction($request, $message, array $reasons = [])
    {
    }
    /**
     * @return void
     * @param string $message
     */
    public function accessDeniedAction($request, $message, array $reasons = [])
    {
    }
}
