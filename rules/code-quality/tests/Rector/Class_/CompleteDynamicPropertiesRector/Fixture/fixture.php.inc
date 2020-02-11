<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SomeClass
{
    public function set()
    {
        $this->value = 5;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SomeClass
{
    public int $value;
    public function set()
    {
        $this->value = 5;
    }
}

?>
