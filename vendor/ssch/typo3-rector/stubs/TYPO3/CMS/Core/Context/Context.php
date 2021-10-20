<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Context;

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
     * @param \TYPO3\CMS\Core\Context\AspectInterface $aspect
     */
    public function setAspect($name, $aspect)
    {
    }
}
