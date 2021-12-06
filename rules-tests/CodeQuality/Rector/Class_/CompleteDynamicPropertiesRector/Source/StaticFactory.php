<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Source;

final class StaticFactory
{
    public $timestamp;

    public static function now()
    {
        return new static();
    }
}
