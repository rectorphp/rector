<?php

namespace Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Fixture;

use Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

class SkipExisting implements SomeInterface
{
    use SomeTrait;
}
