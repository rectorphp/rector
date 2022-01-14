<?php

declare(strict_types=1);

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source;

final class InstantiableViaNamedConstructor
{
    public static function make(int $value): InstantiableViaNamedConstructor
    {
        return new InstantiableViaNamedConstructor($value);
    }

    public function __construct(public int $value)
    {
    }
}
