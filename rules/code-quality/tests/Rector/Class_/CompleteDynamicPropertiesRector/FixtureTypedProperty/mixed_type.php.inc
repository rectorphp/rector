<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\FixtureTypedProperty;

class MixedAnotherType
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

class MixedAnotherType
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
