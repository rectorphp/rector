<?php

namespace RectorPrefix20210630\TYPO3\CMS\Core\Context;

if (\class_exists('TYPO3\\CMS\\Core\\Context\\Context')) {
    return;
}
class Context
{
    /**
     * @return void
     * @param string $name
     * @param string $property
     */
    public function getPropertyFromAspect($name, $property, $default = null)
    {
    }
    /**
     * @return void
     * @param string $name
     */
    public function setAspect($name, \RectorPrefix20210630\TYPO3\CMS\Core\Context\AspectInterface $aspect)
    {
    }
}
