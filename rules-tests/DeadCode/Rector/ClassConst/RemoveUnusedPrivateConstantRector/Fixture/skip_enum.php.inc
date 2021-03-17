<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector\Source\AbstractEnum;

class SkipEnum extends AbstractEnum
{
    private const ENUM_MAGIC = 'yes';
}
