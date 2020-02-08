<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source\SomeProtectedParentClass;

class SkipParentClassProtected extends SomeProtectedParentClass
{
    protected const SOME_CONST = 'changed_value';
}
