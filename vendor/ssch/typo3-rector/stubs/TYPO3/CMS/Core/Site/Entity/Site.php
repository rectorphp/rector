<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Site\Entity;

use RectorPrefix20211020\TYPO3\CMS\Core\Routing\PageRouter;
if (\class_exists('TYPO3\\CMS\\Core\\Site\\Entity\\Site')) {
    return;
}
class Site
{
    /**
     * @return \TYPO3\CMS\Core\Routing\PageRouter
     */
    public function getRouter()
    {
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Routing\PageRouter();
    }
}
