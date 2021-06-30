<?php

namespace RectorPrefix20210630\TYPO3\CMS\Core\Resource\Exception;

use Exception;
if (\class_exists('TYPO3\\CMS\\Core\\Resource\\Exception\\InvalidPathException')) {
    return;
}
class InvalidPathException extends \Exception
{
}
