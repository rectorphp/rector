<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class MultipleProperties
{
    private $x;
    private $y;

    public function run()
    {
        return isset($this->x, $this->y);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

class MultipleProperties
{
    private $x;
    private $y;

    public function run()
    {
        return $this->x !== null && $this->y !== null;
    }
}

?>
