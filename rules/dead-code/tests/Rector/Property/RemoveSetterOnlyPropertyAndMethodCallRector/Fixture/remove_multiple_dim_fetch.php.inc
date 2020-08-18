<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class RemoveMultipleDimFetch
{
    private $name;

    public function setName($name)
    {
        $this->name[][][][][][][] = $name;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class RemoveMultipleDimFetch
{
}

?>
