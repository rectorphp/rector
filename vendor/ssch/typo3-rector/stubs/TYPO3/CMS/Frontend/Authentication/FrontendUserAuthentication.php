<?php

namespace RectorPrefix20211111\TYPO3\CMS\Frontend\Authentication;

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
