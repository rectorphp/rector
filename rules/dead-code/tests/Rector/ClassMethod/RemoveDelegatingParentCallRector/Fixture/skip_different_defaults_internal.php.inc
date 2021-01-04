<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Throwable;

class DefaultOverrideException extends \Exception
{
    public function __construct($message = "My special message", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
class NoOverrideException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
