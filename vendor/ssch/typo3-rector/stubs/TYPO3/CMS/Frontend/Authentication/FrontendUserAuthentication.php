<?php

namespace RectorPrefix20211009\TYPO3\CMS\Frontend\Authentication;

if (\class_exists('TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication')) {
    return;
}
class FrontendUserAuthentication
{
    /**
     * @return mixed
     */
    public function getKey($type, $key)
    {
        return null;
    }
}
