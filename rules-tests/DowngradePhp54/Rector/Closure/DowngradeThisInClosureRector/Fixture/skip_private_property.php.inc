<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class SkipPrivateProperty
{
    private $property = 'test';

    public function run()
    {
        $function = function () {
            echo $this->property;
        };

        $function();
    }
}
