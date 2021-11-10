<?php

namespace RectorPrefix20211110\TYPO3\CMS\Extbase\Mvc\Cli;

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
     * @param mixed[]|null $autocomplete
     */
    public function askAndValidate($question, $validator, $attempts = null, $default = null, $autocomplete = null)
    {
    }
}
