<?php

namespace Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SkipExisting implements SomeInterface
{
    use SomeTrait;
}
