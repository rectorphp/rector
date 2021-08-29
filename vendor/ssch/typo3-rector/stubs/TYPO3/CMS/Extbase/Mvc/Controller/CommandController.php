<?php

namespace RectorPrefix20210829\TYPO3\CMS\Extbase\Mvc\Controller;

if (\class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\CommandController')) {
    return;
}
class CommandController
{
    /**
     * @return void
     */
    protected function getBackendUserAuthentication()
    {
    }
}
