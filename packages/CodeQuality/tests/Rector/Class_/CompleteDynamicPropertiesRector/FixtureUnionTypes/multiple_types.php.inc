<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\FixtureUnionTypes;

class MultipleTypes
{
    public function set()
    {
        $this->value = 5;

        $this->value = 'hey';

        $this->value = false;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\FixtureUnionTypes;

class MultipleTypes
{
    public int|string|bool $value;
    public function set()
    {
        $this->value = 5;

        $this->value = 'hey';

        $this->value = false;
    }
}

?>
