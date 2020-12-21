<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class Property
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

class Property
{
    private $x;
    private $y;

    public function run()
    {
        $this->x !== null;
        $this->y !== null;
        $this->x !== null && $this->y !== null;
    }
}

?>
