<?php

namespace Rector\Tests\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $a = [[], [1, 2, 3], ['foo' => 'bar']];

        return [$a];
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $a = array(array(), array(1, 2, 3), array('foo' => 'bar'));

        return array($a);
    }
}

?>
