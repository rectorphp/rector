<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class FallbackForComplex
{
    public function run($items, \stdClass $someObject)
    {
        for ($i = 5; $i <= count($someObject->getItems() + 10); $i++) {
            echo $items[$i];
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class FallbackForComplex
{
    public function run($items, \stdClass $someObject)
    {
        $itemsCount = count($someObject->getItems() + 10);
        for ($i = 5; $i <= $itemsCount; $i++) {
            echo $items[$i];
        }
    }
}

?>
