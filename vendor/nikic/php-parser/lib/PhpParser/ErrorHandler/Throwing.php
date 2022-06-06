<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\ErrorHandler;

use RectorPrefix20220606\PhpParser\Error;
use RectorPrefix20220606\PhpParser\ErrorHandler;
/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements ErrorHandler
{
    public function handleError(Error $error)
    {
        throw $error;
    }
}
