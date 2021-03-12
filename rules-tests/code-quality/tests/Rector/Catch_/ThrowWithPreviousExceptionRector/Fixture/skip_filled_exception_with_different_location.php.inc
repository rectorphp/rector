<?php

namespace Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\Fixture;

use Exception;
use Throwable;

class SkipFilledExceptionWithDifferentLocation
{
    public function run()
    {
        try {
        } catch (Throwable $throwable) {
            throw new BadRequestHttpException('message some', $throwable);
        }
    }
}

class BadRequestHttpException extends  Exception
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct('message', 400, $previous);
    }
}
