<?php

namespace RectorPrefix20210630\TYPO3\CMS\Frontend\ContentObject;

use RectorPrefix20210630\TYPO3\CMS\Core\Utility\GeneralUtility;
use RectorPrefix20210630\TYPO3\CMS\Frontend\Page\PageRepository;
if (\class_exists('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer')) {
    return;
}
class ContentObjectRenderer
{
    /**
     * @return void
     */
    public function RECORDS(array $config)
    {
    }
    /**
     * @return void
     * @param string $string
     */
    public function cObjGetSingle($string, array $config)
    {
    }
    /**
     * @return void
     */
    public function enableFields($table, $show_hidden = \false, array $ignore_array = [])
    {
    }
    /**
     * @return void
     */
    public function getSubpart($content, $marker)
    {
    }
    /**
     * @return void
     */
    public function substituteSubpart($content, $marker, $subpartContent, $recursive = 1)
    {
    }
    /**
     * @return void
     */
    public function substituteSubpartArray($content, array $subpartsContent)
    {
    }
    /**
     * @return void
     */
    public function substituteMarker($content, $marker, $markContent)
    {
    }
    /**
     * @return void
     */
    public function substituteMarkerArrayCached($content, array $markContentArray = null, array $subpartContentArray = null, array $wrappedSubpartContentArray = null)
    {
    }
    /**
     * @return void
     */
    public function substituteMarkerArray($content, array $markContentArray, $wrap = '', $uppercase = \false, $deleteUnused = \false)
    {
    }
    /**
     * @return void
     */
    public function substituteMarkerInObject(&$tree, array $markContentArray)
    {
    }
    /**
     * @return void
     */
    public function substituteMarkerAndSubpartArrayRecursive($content, array $markersAndSubparts, $wrap = '', $uppercase = \false, $deleteUnused = \false)
    {
    }
    /**
     * @return void
     */
    public function fillInMarkerArray(array $markContentArray, array $row, $fieldList = '', $nl2br = \true, $prefix = 'FIELD_', $HSC = \false)
    {
    }
    /**
     * @return void
     */
    public function fileResource($file)
    {
    }
    public function getQueryArguments($conf, $overruleQueryArguments = [], $forceOverruleArguments = \false)
    {
    }
}
