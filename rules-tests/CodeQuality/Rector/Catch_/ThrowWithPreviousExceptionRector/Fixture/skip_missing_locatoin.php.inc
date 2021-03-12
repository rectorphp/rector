<?php

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\Fixture;

use Exception;
use Throwable;

class SkipMissingLocation
{
    public function run()
    {
        try {
        } catch (Throwable $throwable) {
            throw new MissingPreviousException('message some');
        }
    }
}

class MissingPreviousException extends  Exception
{
    public function __construct(string $message = null, int $code = 0, array $headers = [])
    {
        parent::__construct('message', 400);
    }
}
