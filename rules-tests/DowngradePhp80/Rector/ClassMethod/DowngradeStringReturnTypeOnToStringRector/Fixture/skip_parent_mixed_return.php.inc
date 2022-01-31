<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Source\ParentClassWithToStringMixedReturn;

class SkipParentMixedReturn extends ParentClassWithToStringMixedReturn
{
    public function __toString()
    {
        return 'value';
    }
}
