<?php

namespace Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Transform\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SkipExisting implements SomeInterface
{
    use SomeTrait;
}
