<?php

namespace RectorPrefix20210603\TYPO3\CMS\Extbase\Validation\Exception;

if (\class_exists('TYPO3\\CMS\\Extbase\\Validation\\Exception\\NoValidatorFoundException')) {
    return;
}
class NoValidatorFoundException
{
}
