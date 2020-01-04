<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class MethodCallCount
{
    public function run($items, \stdClass $someObject)
    {
        for ($i = 5; $i <= count($someObject->getItems()); $i++) {
            echo $items[$i];
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class MethodCallCount
{
    public function run($items, \stdClass $someObject)
    {
        $getItemsCount = count($someObject->getItems());
        for ($i = 5; $i <= $getItemsCount; $i++) {
            echo $items[$i];
        }
    }
}

?>
