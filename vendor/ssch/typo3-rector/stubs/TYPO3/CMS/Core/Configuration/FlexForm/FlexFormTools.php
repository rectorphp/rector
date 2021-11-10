<?php

namespace RectorPrefix20211110\TYPO3\CMS\Core\Configuration\FlexForm;

if (\class_exists('TYPO3\\CMS\\Core\\Configuration\\FlexForm\\FlexFormTools')) {
    return;
}
class FlexFormTools
{
    /**
     * @param string $pathArray
     * @param array $array
     * @return mixed Value returned
     */
    public function &getArrayValueByPath($pathArray, &$array)
    {
    }
    /**
     * @param string $pathArray
     * @param array $array
     * @param mixed $value
     *
     * @return mixed
     */
    public function setArrayValueByPath($pathArray, &$array, $value)
    {
    }
}
