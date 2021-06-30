<?php

namespace RectorPrefix20210630\TYPO3\CMS\Extbase\Mvc\View;

if (\interface_exists('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface')) {
    return;
}
interface ViewInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function assign($key, $value);
    /**
     * @return void
     */
    public function assignMultiple(array $values);
    /**
     * @return string
     */
    public function render();
}
