<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class SkipElseNotNullValue
{
    public function run()
    {
        $v = ['value' => null];
        return array_key_exists('value', $v) ? $v['value'] : $v;
    }
}

?>

