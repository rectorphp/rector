<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\Fixture;

class SkipApi
{
    /**
     * @api
     */
    private const USED_PUBLICLY_IN_ANOTHER_PROJECT = 'publicly';
}
