<?php

namespace Rector\Downgrade\Tests\Rector\Coalesce\DowngradeNullCoalescingOperatorRector\Fixture;

class FixtureClass
{
    public function run()
    {
        $array = [];
        $array['user_id'] ??= 'value';
    }
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Coalesce\DowngradeNullCoalescingOperatorRector\Fixture;

class FixtureClass
{
    public function run()
    {
        $array = [];
        $array['user_id'] = $array['user_id'] ?? 'value';
    }
}

?>
