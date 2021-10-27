<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\ParentSymfonyString;

final class SkipReturnParentClass extends ParentSymfonyString
{
    protected $string = '';

    public function upper(): ParentSymfonyString
    {
        $str = clone $this;
        $str->string = strtoupper($str->string);

        return $str;
    }

}
