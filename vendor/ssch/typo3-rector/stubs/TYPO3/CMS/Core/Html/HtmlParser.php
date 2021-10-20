<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Html;

if (\class_exists('TYPO3\\CMS\\Core\\Html\\HtmlParser')) {
    return;
}
class HtmlParser
{
    /**
     * @return string
     */
    public function getSubpart($content, $marker)
    {
        return 'subpart';
    }
    /**
     * @return string
     */
    public function substituteSubpart($content, $marker, $subpartContent, $recursive = \true, $keepMarker = \false)
    {
        return 'subpart';
    }
    /**
     * @return string
     * @param mixed[] $subpartsContent
     */
    public function substituteSubpartArray($content, $subpartsContent)
    {
        return 'html';
    }
    /**
     * @return string
     */
    public function substituteMarker($content, $marker, $markContent)
    {
        return 'html';
    }
    /**
     * @return string
     */
    public function substituteMarkerArray($content, $markContentArray, $wrap = '', $uppercase = \false, $deleteUnused = \false)
    {
        return 'html';
    }
    /**
     * @return string
     * @param mixed[] $markersAndSubparts
     */
    public function substituteMarkerAndSubpartArrayRecursive($content, $markersAndSubparts, $wrap = '', $uppercase = \false, $deleteUnused = \false)
    {
        return 'html';
    }
    /**
     * @return string
     */
    public function XHTML_clean($content)
    {
        return 'html';
    }
    /**
     * @return string
     */
    public function HTMLcleaner($content, $tags = [], $keepAll = 0, $hSC = 0, $addConfig = [])
    {
        return 'html';
    }
}
