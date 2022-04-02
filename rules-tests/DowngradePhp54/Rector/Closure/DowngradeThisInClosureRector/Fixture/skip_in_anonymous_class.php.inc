<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class SkipInAnonymousClass
{
    public $property = 'test';

    public function run()
    {
        $function = function () {
            new class {
                public function test()
                {
                    echo $this->property;
                }
            };
        };

        $function();
    }
}
