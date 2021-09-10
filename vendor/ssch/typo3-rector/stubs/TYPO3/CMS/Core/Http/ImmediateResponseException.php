<?php

namespace RectorPrefix20210910\TYPO3\CMS\Core\Http;

use Exception;
if (\class_exists('TYPO3\\CMS\\Core\\Http\\ImmediateResponseException')) {
    return;
}
class ImmediateResponseException extends \Exception
{
}
