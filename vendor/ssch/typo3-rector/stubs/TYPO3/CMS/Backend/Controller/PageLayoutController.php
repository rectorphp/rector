<?php

namespace RectorPrefix20210827\TYPO3\CMS\Backend\Controller;

use RectorPrefix20210827\TYPO3\CMS\Backend\Template\ModuleTemplate;
if (\class_exists('TYPO3\\CMS\\Backend\\Controller\\PageLayoutController')) {
    return;
}
class PageLayoutController
{
    /**
     * @return void
     */
    public function printContent()
    {
    }
    /**
     * @return \TYPO3\CMS\Backend\Template\ModuleTemplate
     */
    public function getModuleTemplate()
    {
        return new \RectorPrefix20210827\TYPO3\CMS\Backend\Template\ModuleTemplate();
    }
}
