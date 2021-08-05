<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use Exception;

class SkipArrayMapExtendsException extends Exception
{
    public static function fromError(array $data)
    {
        return new self(current(array_map([self::class, 'message'], $data)));
    }

    private static function message(string $data)
    {
        return $data;
    }
}
