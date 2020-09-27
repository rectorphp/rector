<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class SkipElseFalseValue
{
    public function run()
    {
        $v = ['value' => null];
        return array_key_exists('value', $v) ? $v['value'] : false;
    }
}

?>
