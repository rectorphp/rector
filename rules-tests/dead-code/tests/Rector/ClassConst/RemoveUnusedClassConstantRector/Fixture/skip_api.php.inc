<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class SkipApi
{
    /**
     * @api
     */
    public const USED_PUBLICLY_IN_ANOTHER_PROJECT = 'publicly';
}
