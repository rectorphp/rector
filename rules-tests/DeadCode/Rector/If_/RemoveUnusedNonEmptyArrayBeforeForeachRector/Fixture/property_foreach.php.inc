<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

final class PropertyForeach
{
    private $items = [];

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function run()
    {
        if ($this->items) {
            foreach ($this->items as $item) {
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

final class PropertyForeach
{
    private $items = [];

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function run()
    {
        foreach ($this->items as $item) {
        }
    }
}

?>
