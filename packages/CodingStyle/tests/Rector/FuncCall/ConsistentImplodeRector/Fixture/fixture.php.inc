<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector\Fixture;

class SomeClass
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

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector\Fixture;

class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode('', $items);
        $itemsAsStrings = implode('|', $items);

        $itemsAsStrings = implode('|', $items);
    }
}

?>
