<?php

namespace RectorPrefix20210630\TYPO3\CMS\Core\Site\Entity;

if (\class_exists('TYPO3\\CMS\\Core\\Site\\Entity\\SiteLanguage')) {
    return;
}
class SiteLanguage
{
    /**
     * @return string
     */
    public function getTwoLetterIsoCode()
    {
        return 'ch';
    }
}
