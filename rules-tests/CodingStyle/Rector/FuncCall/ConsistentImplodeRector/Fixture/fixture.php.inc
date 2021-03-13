<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentImplodeRector\Fixture;

class Fixture
{
    public function run(array $items)
    {
        $itemsAsStrings = implode($items);
        $itemsAsStrings = implode($items, '|');

        $itemsAsStrings = implode('|', $items);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentImplodeRector\Fixture;

class Fixture
{
    public function run(array $items)
    {
        $itemsAsStrings = implode('', $items);
        $itemsAsStrings = implode('|', $items);

        $itemsAsStrings = implode('|', $items);
    }
}

?>
