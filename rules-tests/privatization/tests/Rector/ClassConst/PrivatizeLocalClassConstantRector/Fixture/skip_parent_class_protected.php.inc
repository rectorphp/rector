<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\SomeProtectedParentClass;

class SkipParentClassProtected extends SomeProtectedParentClass
{
    protected const SOME_CONST = 'changed_value';
}
