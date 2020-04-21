<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Source;

abstract class SomeProtectedParentClass
{
    protected const SOME_CONST = '...';
}
