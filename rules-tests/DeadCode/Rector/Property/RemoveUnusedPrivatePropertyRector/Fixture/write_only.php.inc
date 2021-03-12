<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class WriteOnly
{
    private $seemsUnused;

    public function getValue()
    {
        foreach ([1, 2, 3] as $key) {
            $this->seemsUnused[] = 'foo';
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class WriteOnly
{
    public function getValue()
    {
        foreach ([1, 2, 3] as $key) {
        }
    }
}

?>
