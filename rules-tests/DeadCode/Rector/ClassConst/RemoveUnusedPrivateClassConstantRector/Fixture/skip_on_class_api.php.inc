<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

/**
 * @api
 */
class SkipOnClassApi
{
    private const USED_PUBLICLY_IN_ANOTHER_PROJECT = 'publicly';
}
