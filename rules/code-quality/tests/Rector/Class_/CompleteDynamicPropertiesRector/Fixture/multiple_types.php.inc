<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class MultipleTypes
{
    public function set()
    {
        $this->value = 5;

        $this->value = 'hey';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class MultipleTypes
{
    /**
     * @var int|string
     */
    public $value;
    public function set()
    {
        $this->value = 5;

        $this->value = 'hey';
    }
}

?>
