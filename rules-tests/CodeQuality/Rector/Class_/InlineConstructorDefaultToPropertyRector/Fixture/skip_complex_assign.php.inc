<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\Fixture;

final class SkipComplexAssign
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
