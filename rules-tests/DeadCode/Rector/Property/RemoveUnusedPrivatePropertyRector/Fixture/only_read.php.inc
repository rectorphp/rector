<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class OnlyRead
{
    private $unused;

    public function run()
    {
        if ($this->unused) {
            echo "123";
        }

        return 5;
    }
}

?>
    -----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class OnlyRead
{
    public function run()
    {
        return 5;
    }
}

?>
