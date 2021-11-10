<?php

namespace RectorPrefix20211110\TYPO3\CMS\Extbase\Mvc\Controller;

use RectorPrefix20211110\TYPO3\CMS\Extbase\Mvc\Request;
if (\class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\AbstractController')) {
    return;
}
abstract class AbstractController
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * AbstractController constructor.
     */
    public function __construct()
    {
        $this->request = new \RectorPrefix20211110\TYPO3\CMS\Extbase\Mvc\Request();
    }
}
