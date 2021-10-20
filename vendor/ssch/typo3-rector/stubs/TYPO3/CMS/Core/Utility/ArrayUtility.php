<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Utility;

if (\class_exists('TYPO3\\CMS\\Core\\Utility\\ArrayUtility')) {
    return;
}
class ArrayUtility
{
    /**
     * @return void
     * @param mixed[] $original
     * @param mixed[] $overrule
     */
    public static function mergeRecursiveWithOverrule(&$original, $overrule, $addKeys = \true, $includeEmptyValues = \true, $enableUnsetFeature = \true)
    {
    }
    /**
     * @return void
     */
    public static function getValueByPath()
    {
    }
    /**
     * @return void
     */
    public static function setValueByPath()
    {
    }
    /**
     * @return void
     */
    public static function removeByPath()
    {
    }
    /**
     * @return void
     */
    public static function sortArrayWithIntegerKeys()
    {
    }
    /**
     * @return bool
     * @param mixed[] $in_array
     */
    public static function inArray($in_array, $item)
    {
        return \true;
    }
}
