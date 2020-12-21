<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

class SkipInProperty
{
    private static $listen = [
        'run' => [self::class, 'run'],
    ];

    public function __invoke()
    {
        $handler = self::$listen['run'];
        call_user_func($handler, 'data');
    }

    public function run(string $data)
    {
    }
}
