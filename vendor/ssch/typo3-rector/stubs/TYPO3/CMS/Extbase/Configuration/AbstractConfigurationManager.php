<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Configuration;

if (\class_exists('TYPO3\\CMS\\Extbase\\Configuration\\AbstractConfigurationManager')) {
    return;
}
abstract class AbstractConfigurationManager
{
    /**
     * @param string $extensionName
     * @param string $pluginName
     */
    protected abstract function getSwitchableControllerActions($extensionName, $pluginName);
}
