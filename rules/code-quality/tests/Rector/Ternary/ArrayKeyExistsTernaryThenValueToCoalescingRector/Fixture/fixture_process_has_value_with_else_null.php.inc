<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class ProcessHasValue
{
    public function run()
    {
        $v = ['value' => 'whatever'];
        return array_key_exists('value', $v) ? $v['value'] : null;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class ProcessHasValue
{
    public function run()
    {
        $v = ['value' => 'whatever'];
        return $v['value'] ?? null;
    }
}

?>
