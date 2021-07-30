<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SomeFunctionCall
{
    private $prop;

    public function doThing()
    {
        $this->prop = foo();

        $this->prop[bar()] = foo();

        return $this->prop[bar()] = foo();
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SomeFunctionCall
{
    public function doThing()
    {
        bar();

        bar();

        return foo();
    }
}

?>
