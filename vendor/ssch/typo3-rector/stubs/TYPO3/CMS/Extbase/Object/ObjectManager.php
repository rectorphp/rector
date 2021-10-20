<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Object;

if (\class_exists('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')) {
    return;
}
class ObjectManager implements \RectorPrefix20211020\TYPO3\CMS\Extbase\Object\ObjectManagerInterface
{
    /**
     * @param $objectName
     *
     * @return object
     */
    public function get($objectName)
    {
        return new $objectName(\func_get_args());
    }
}
