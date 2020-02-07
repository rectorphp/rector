<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class AlreadyExistingItemsCount
{
    public function run($items, \stdClass $someObject)
    {
        $itemsCount = 500000;

        for ($i = 5; $i <= count($someObject->getItems() + 10); $i++) {
            echo $items[$i];
        }

        return $itemsCount;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class AlreadyExistingItemsCount
{
    public function run($items, \stdClass $someObject)
    {
        $itemsCount = 500000;
        $itemsCount2 = count($someObject->getItems() + 10);

        for ($i = 5; $i <= $itemsCount2; $i++) {
            echo $items[$i];
        }

        return $itemsCount;
    }
}

?>
