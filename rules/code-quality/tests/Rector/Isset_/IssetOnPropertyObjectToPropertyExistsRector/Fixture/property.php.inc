<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass
{
    private $x;
    private $y;

    public function run()
    {
        isset($this->x);
        isset($this->y);
        isset($this->x) && isset($this->y);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class SomeClass
{
    private $x;
    private $y;

    public function run()
    {
        property_exists($this, 'x') && $this->x !== null;
        property_exists($this, 'y') && $this->y !== null;
        property_exists($this, 'x') && $this->x !== null && (property_exists($this, 'y') && $this->y !== null);
    }
}

?>
