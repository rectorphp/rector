<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\Fixture;

final class SkipConditionalDefault
{
    private $name;

    public function __construct()
    {
        if (mt_rand(0, 1)) {
            $this->name = 'John';
        }
    }
}
