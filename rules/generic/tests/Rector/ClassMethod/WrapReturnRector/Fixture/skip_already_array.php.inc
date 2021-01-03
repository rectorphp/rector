<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class SkipAllReadyArray extends SomeReturnClass
{
    public function getItem()
    {
        return [1];
    }
}
