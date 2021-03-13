<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

final class PropertyMaybeExistsAfterInstantiation
{
    public function init()
    {
        $this->x = 'a';
    }

    public function run()
    {
        $obj = new self();
        $obj->init();

        isset($obj->x);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

final class PropertyMaybeExistsAfterInstantiation
{
    public function init()
    {
        $this->x = 'a';
    }

    public function run()
    {
        $obj = new self();
        $obj->init();

        property_exists($obj, 'x') && $obj->x !== null;
    }
}

?>
