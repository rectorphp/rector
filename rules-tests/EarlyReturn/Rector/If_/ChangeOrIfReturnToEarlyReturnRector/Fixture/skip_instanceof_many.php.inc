<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

final class SkipInstanceOfMany
{
    public function run($value)
    {
        // a comment
        if ($value instanceof String_ || $value instanceof DNumber || $value instanceof LNumber) {
            return null;
        }

        return 'another';
    }
}
