<?php

namespace RectorPrefix20211027\TYPO3\CMS\Taskcenter\Controller;

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
