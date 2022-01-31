<?php


namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Source;

abstract class ParentClassWithToString
{
    public function __toString(): string
    {
        return 'value';
    }
}
