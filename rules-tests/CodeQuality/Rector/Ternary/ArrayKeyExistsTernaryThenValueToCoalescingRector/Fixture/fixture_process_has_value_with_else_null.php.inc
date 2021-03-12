<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\Fixture;

class FixtureProcessHasValueWithElseNull
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

class FixtureProcessHasValueWithElseNull
{
    public function run()
    {
        $v = ['value' => 'whatever'];
        return $v['value'] ?? null;
    }
}

?>
