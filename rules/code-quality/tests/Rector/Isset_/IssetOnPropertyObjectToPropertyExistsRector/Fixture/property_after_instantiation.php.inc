<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass2
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

class SomeClass2
{
    public $x;
    public $y;

    public function run()
    {
        $obj = new self();
        property_exists($obj, 'x') && $obj->x !== null;
        property_exists($obj, 'y') && $obj->y !== null;
        property_exists($obj, 'x') && $obj->x !== null && (property_exists($obj, 'y') && $obj->y !== null);
    }
}

?>
