<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class SkipMismatch
{
    public function run($values, $keyToMatch)
    {
        $result = array_key_exists($keyToMatch, $values) ? $values[$keyToMatch2] : null;
    }
}
