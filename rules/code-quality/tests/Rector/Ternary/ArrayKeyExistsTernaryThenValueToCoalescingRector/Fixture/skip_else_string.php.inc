<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class SkipElseString
{
    public function run()
    {
        $v = ['value' => null];
        return array_key_exists('value', $v) ? $v['value'] : 'a string value';
    }
}

?>
