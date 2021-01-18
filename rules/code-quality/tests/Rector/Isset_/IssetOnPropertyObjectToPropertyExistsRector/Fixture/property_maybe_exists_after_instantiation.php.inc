<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class PropertyMaybeExistsAfterInstantiation
{
    public function init()
    {
        $this->x = 'a';
        $this->y = 'b';
    }

    public function run()
    {
        $obj = new self();
        $obj->init();

        isset($obj->x);
        isset($obj->y);
        isset($obj->x) && isset($obj->y);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class PropertyMaybeExistsAfterInstantiation
{
    public function init()
    {
        $this->x = 'a';
        $this->y = 'b';
    }

    public function run()
    {
        $obj = new self();
        $obj->init();

        property_exists($obj, 'x') && $obj->x !== null;
        property_exists($obj, 'y') && $obj->y !== null;
        property_exists($obj, 'x') && $obj->x !== null && (property_exists($obj, 'y') && $obj->y !== null);
    }
}

?>
