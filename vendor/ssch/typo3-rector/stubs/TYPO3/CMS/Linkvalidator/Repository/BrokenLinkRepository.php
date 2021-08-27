<?php

namespace RectorPrefix20210827\TYPO3\CMS\Linkvalidator\Repository;

if (\class_exists('TYPO3\\CMS\\Linkvalidator\\Repository\\BrokenLinkRepository')) {
    return;
}
class BrokenLinkRepository
{
    /**
     * @param string $linkTarget
     * @return int
     */
    public function getNumberOfBrokenLinks($linkTarget)
    {
        $linkTarget = (string) $linkTarget;
        return 1;
    }
    /**
     * @param string $linkTarget
     * @return bool
     */
    public function isLinkTargetBrokenLink($linkTarget)
    {
        $linkTarget = (string) $linkTarget;
        return \true;
    }
}
