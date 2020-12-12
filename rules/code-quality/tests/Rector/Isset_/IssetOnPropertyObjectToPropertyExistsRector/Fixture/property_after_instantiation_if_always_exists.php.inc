<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class PropertyAfterInstantiationIfAlwaysExists
{
    public $x;
    public $y;

    public function run()
    {
        $obj = new self();
        isset($obj->x);
        isset($obj->y);
        isset($obj->x) && isset($obj->y);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class PropertyAfterInstantiationIfAlwaysExists
{
    public $x;
    public $y;

    public function run()
    {
        $obj = new self();
        $obj->x !== null;
        $obj->y !== null;
        $obj->x !== null && $obj->y !== null;
    }
}

?>
