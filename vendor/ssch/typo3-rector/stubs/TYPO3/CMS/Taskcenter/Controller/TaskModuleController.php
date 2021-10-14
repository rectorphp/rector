<?php

namespace RectorPrefix20211014\TYPO3\CMS\Taskcenter\Controller;

if (\class_exists('TYPO3\\CMS\\Taskcenter\\Controller\\TaskModuleController')) {
    return;
}
class TaskModuleController
{
    /**
     * @var string
     */
    public $content = '';
    /**
     * @return void
     */
    public function printContent()
    {
    }
}
