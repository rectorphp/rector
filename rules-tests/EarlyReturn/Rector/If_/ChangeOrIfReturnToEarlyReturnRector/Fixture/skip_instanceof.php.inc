<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\String_;

final class SkipInstanceOf
{
    public function run($value)
    {
        // a comment
        if ($value instanceof String_ || $value instanceof DNumber) {
            return null;
        }

        return 'another';
    }
}
