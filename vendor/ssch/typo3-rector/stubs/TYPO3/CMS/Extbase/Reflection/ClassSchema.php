<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Reflection;

if (\class_exists('TYPO3\\CMS\\Extbase\\Reflection\\ClassSchema')) {
    return;
}
class ClassSchema
{
    /**
     * @return mixed[]
     */
    public function getProperty($propertyName)
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getTags()
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getMethod($methodName)
    {
        return [];
    }
    /**
     * @return bool
     */
    public function hasMethod($methodName)
    {
        return \false;
    }
    /**
     * @return mixed[]
     */
    public function getProperties()
    {
        return [];
    }
}
