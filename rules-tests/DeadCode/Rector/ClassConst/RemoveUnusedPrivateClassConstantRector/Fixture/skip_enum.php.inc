<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Source\AbstractEnum;

class SkipEnum extends AbstractEnum
{
    private const ENUM_MAGIC = 'yes';
}
