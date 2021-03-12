<?php

namespace Rector\Tests\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\SomeProtectedParentClass;

class SkipParentClassProtected extends SomeProtectedParentClass
{
    protected const SOME_CONST = 'changed_value';
}
