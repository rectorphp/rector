<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class TypedProperty
{
    public int $property = 1;

    public function run()
    {
        $value = (int) $this->property;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class TypedProperty
{
    public int $property = 1;

    public function run()
    {
        $value = $this->property;
    }
}

?>
