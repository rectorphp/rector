<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Site;

use RectorPrefix20211020\TYPO3\CMS\Core\Site\Entity\Site;
if (\class_exists('TYPO3\\CMS\\Core\\Site\\SiteFinder')) {
    return;
}
class SiteFinder
{
    /**
     * @param int $pageId
     * @param string $mountPointParameter
     * @return \TYPO3\CMS\Core\Site\Entity\Site
     * @param mixed[]|null $rootLine
     */
    public function getSiteByPageId($pageId, $rootLine = null, $mountPointParameter = null)
    {
        $pageId = (int) $pageId;
        $mountPointParameter = (string) $mountPointParameter;
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Site\Entity\Site();
    }
}
