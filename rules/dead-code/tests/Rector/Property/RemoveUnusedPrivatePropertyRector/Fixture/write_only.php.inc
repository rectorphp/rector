<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class WriteOnly
{
    public function getValue()
    {
        foreach ([1, 2, 3] as $key) {
        }
    }
}

?>
