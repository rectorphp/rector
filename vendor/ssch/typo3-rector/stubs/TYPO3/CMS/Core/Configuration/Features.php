<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Configuration;

if (\class_exists('TYPO3\\CMS\\Core\\Configuration\\Features')) {
    return;
}
class Features
{
    /**
     * @param string $feature
     * @return bool
     */
    public function isFeatureEnabled($feature)
    {
        $feature = (string) $feature;
        return \true;
    }
}
