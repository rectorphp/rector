<?php

namespace Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SkipExisting implements SomeInterface
{
    use SomeTrait;
}
