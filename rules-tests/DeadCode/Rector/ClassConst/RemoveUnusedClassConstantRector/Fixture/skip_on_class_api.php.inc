<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

/**
 * @api
 */
class SkipOnClassApi
{
    public const USED_PUBLICLY_IN_ANOTHER_PROJECT = 'publicly';
}
