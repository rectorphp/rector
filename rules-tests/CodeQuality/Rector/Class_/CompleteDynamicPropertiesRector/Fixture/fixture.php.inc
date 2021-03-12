<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class Fixture
{
    public function set()
    {
        $this->value = 5;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class Fixture
{
    public int $value;
    public function set()
    {
        $this->value = 5;
    }
}

?>
