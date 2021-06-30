<?php

namespace RectorPrefix20210630\TYPO3\CMS\Extbase\Mvc\Cli;

if (\class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Cli\\ConsoleOutput')) {
    return;
}
class ConsoleOutput
{
    /**
     * @return void
     */
    public function select($question, $choices, $default = null, $multiSelect = \false, $attempts = null)
    {
    }
    /**
     * @return void
     */
    public function askAndValidate($question, $validator, $attempts = null, $default = null, array $autocomplete = null)
    {
    }
}
