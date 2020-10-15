<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

use Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Source\AbstractEnum;

class SkipEnum extends AbstractEnum
{
    public const ENUM_MAGIC = 'yes';
}
