<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\Fixture;

class SomePropertyFetch
{
    public function run()
    {
        $username = $this->property ?? 'value';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\Fixture;

class SomePropertyFetch
{
    public function run()
    {
        $username = isset($this->property) ? $this->property : 'value';
    }
}

?>
