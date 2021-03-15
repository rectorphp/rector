<?php

namespace Rector\Tests\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector\Fixture;

class Fixture
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

namespace Rector\Tests\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector\Fixture;

class Fixture
{
    public function run()
    {
        $array = [];
        $array['user_id'] = $array['user_id'] ?? 'value';
    }
}

?>
