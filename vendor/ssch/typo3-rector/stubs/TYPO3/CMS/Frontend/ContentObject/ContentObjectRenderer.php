<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\ContentObject;

use RectorPrefix20211020\TYPO3\CMS\Core\Utility\GeneralUtility;
use RectorPrefix20211020\TYPO3\CMS\Frontend\Page\PageRepository;
if (\class_exists('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer')) {
    return;
}
class ContentObjectRenderer
{
    /**
     * @return void
     * @param mixed[] $config
     */
    public function RECORDS($config)
    {
    }
    /**
     * @return void
     * @param string $string
     * @param mixed[] $config
     */
    public function cObjGetSingle($string, $config)
    {
    }
    /**
     * @return void
     * @param mixed[] $ignore_array
     */
    public function enableFields($table, $show_hidden = \false, $ignore_array = [])
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
     * @param mixed[] $subpartsContent
     */
    public function substituteSubpartArray($content, $subpartsContent)
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
     * @param mixed[]|null $markContentArray
     * @param mixed[]|null $subpartContentArray
     * @param mixed[]|null $wrappedSubpartContentArray
     */
    public function substituteMarkerArrayCached($content, $markContentArray = null, $subpartContentArray = null, $wrappedSubpartContentArray = null)
    {
    }
    /**
     * @return void
     * @param mixed[] $markContentArray
     */
    public function substituteMarkerArray($content, $markContentArray, $wrap = '', $uppercase = \false, $deleteUnused = \false)
    {
    }
    /**
     * @return void
     * @param mixed[] $markContentArray
     */
    public function substituteMarkerInObject(&$tree, $markContentArray)
    {
    }
    /**
     * @return void
     * @param mixed[] $markersAndSubparts
     */
    public function substituteMarkerAndSubpartArrayRecursive($content, $markersAndSubparts, $wrap = '', $uppercase = \false, $deleteUnused = \false)
    {
    }
    /**
     * @return void
     * @param mixed[] $markContentArray
     * @param mixed[] $row
     */
    public function fillInMarkerArray($markContentArray, $row, $fieldList = '', $nl2br = \true, $prefix = 'FIELD_', $HSC = \false)
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
