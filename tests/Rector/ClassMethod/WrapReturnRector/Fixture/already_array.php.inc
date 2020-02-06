<?php

namespace Rector\Core\Tests\Rector\ClassMethod\WrapReturnRector\Fixture;

use Rector\Core\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class AllReadyArray extends SomeReturnClass
{
    public function getItem()
    {
        return [1];
    }
}
