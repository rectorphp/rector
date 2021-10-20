<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Html;

if (\class_exists('TYPO3\\CMS\\Core\\Html\\RteHtmlParser')) {
    return;
}
class RteHtmlParser
{
    /**
     * @return string
     */
    public function HTMLcleaner_db($content)
    {
        return '';
    }
    /**
     * @return mixed[]
     */
    public function getKeepTags($direction = 'rte')
    {
        return [];
    }
    /**
     * @return string
     */
    public function getUrl($url)
    {
        return '';
    }
    /**
     * @return string
     */
    public function siteUrl()
    {
        return '';
    }
}
