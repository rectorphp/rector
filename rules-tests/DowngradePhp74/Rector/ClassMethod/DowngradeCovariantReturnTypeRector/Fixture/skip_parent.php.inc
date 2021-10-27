<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\ParentSymfonyString;

final class SkipParent extends ParentSymfonyString
{
    protected $string = '';

    public function upper(): parent
    {
        $str = clone $this;
        $str->string = strtoupper($str->string);

        return $str;
    }

}
