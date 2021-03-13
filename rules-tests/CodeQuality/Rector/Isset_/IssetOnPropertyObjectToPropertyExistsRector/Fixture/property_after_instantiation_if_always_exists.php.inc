<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

final class PropertyAfterInstantiationIfAlwaysExists
{
    public $x;
    public $y;

    public function run()
    {
        $obj = new self();
        isset($obj->x);
        isset($obj->y);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

final class PropertyAfterInstantiationIfAlwaysExists
{
    public $x;
    public $y;

    public function run()
    {
        $obj = new self();
        $obj->x !== null;
        $obj->y !== null;
    }
}

?>
