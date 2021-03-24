<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

final class Carbon
{
    public $timestamp;

    public static function now()
    {
        return new static();
    }
}

class SkipStaticReturnObject
{
    public function getCurrentTimestamp()
    {
        return Carbon::now()->timestamp;
    }
}