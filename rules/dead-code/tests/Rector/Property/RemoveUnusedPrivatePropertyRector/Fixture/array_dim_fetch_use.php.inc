<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class ArrayDimFetchUse
{
    /**
    * @var string
    */
    private $currentProvider;

    /**
    * @var mixed[]
    */
    private $events = [];

    public function run()
    {
        $this->events[$this->currentProvider] = 1000;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class ArrayDimFetchUse
{
    public function run()
    {
    }
}

?>
