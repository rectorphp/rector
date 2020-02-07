<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class SomeClass
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
        $name = 'Hi' . ' ' . 'John';

        $name = 'Hi'
            . ' '
            . 'John';

        $name = 'Hi' . $name . ' John';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class SomeClass
{
    public function run()
    {
        $name = 'Hi Tom';
        $name = 'Hi John';

        $name = 'Hi John';

        $name = 'Hi' . $name . ' John';
    }
}

?>
