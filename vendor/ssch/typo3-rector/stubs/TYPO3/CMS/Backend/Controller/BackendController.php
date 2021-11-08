<?php

namespace RectorPrefix20211108\TYPO3\CMS\Backend\Controller;

use RectorPrefix20211108\TYPO3\CMS\Core\Page\PageRenderer;
if (\class_exists('TYPO3\\CMS\\Backend\\Controller\\BackendController')) {
    return;
}
class BackendController
{
    /**
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    public function getPageRenderer()
    {
        return new \RectorPrefix20211108\TYPO3\CMS\Core\Page\PageRenderer();
    }
}
