<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use PhpParser\Node\Scalar\String_;

final class SkipOnNodesAsMostlyUsed
{
    public function run(String_ $string)
    {
        $name = $string->value;

        return $name;
    }
}
