<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Routing;

if (\class_exists('TYPO3\\CMS\\Core\\Routing\\PageRouter')) {
    return;
}
class PageRouter
{
    /**
     * @param int $uid
     * @return string
     */
    public function generateUri($uid)
    {
        $uid = (int) $uid;
        return 'foo';
    }
}
