<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector\Source\AbstractEnum;

class SkipEnum extends AbstractEnum
{
    public const ENUM_MAGIC = 'yes';
}
