<?php

namespace Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeInterface;
use Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeParent;

class SkipExisting extends SomeParent implements SomeInterface
{

}
