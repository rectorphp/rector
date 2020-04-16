<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\FixtureTypedProperty;

class MixedType
{
    public function run()
    {
        $this->output = $output;
        //@anything
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\FixtureTypedProperty;

class MixedType
{
    /**
     * @var mixed
     */
    public $output;
    public function run()
    {
        $this->output = $output;
        //@anything
    }
}

?>
