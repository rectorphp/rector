<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class ProcessNullValueWithElseNull
{
    public function run()
    {
        $v = ['value' => null];
        return array_key_exists('value', $v) ? $v['value'] : null;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class ProcessNullValueWithElseNull
{
    public function run()
    {
        $v = ['value' => null];
        return $v['value'] ?? null;
    }
}

?>
