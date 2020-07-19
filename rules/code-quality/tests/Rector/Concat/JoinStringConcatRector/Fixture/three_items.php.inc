<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class ThreeItems
{
    public function run()
    {
        $name = 'Hi' . ' ' . 'John';

        $name = 'Hi' . $name . ' John';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class ThreeItems
{
    public function run()
    {
        $name = 'Hi John';

        $name = 'Hi' . $name . ' John';
    }
}

?>
