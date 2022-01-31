<?php


namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Source;

abstract class ParentClassWithToStringMixedReturn
{
    public function __toString()
    {
        return 'value';
    }
}
