<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Configuration;

if (\class_exists('TYPO3\\CMS\\Core\\Configuration\\ExtensionConfiguration')) {
    return;
}
class ExtensionConfiguration
{
    /**
     * @return array
     * @param string $extension
     * @param string $path
     */
    public function get($extension, $path = '')
    {
        $extension = (string) $extension;
        $path = (string) $path;
        return [];
    }
    /**
     * @return void
     * @param string $extension
     * @param string $path
     */
    public function set($extension, $path = '', $value = null)
    {
    }
}
