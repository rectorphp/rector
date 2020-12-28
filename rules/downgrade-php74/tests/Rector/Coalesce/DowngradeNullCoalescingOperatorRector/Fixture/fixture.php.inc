<?php

namespace Rector\DowngradePhp74\Tests\Rector\Coalesce\DowngradeNullCoalescingOperatorRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\Coalesce\DowngradeNullCoalescingOperatorRector\Fixture;

class Fixture
{
    public function run()
    {
        $array = [];
        $array['user_id'] = $array['user_id'] ?? 'value';
    }
}

?>
