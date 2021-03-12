<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class Fixture
{
    public function run($items)
    {
        for ($i = 5; $i <= count($items); $i++) {
            echo $items[$i];
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\Fixture;

class Fixture
{
    public function run($items)
    {
        $itemsCount = count($items);
        for ($i = 5; $i <= $itemsCount; $i++) {
            echo $items[$i];
        }
    }
}

?>
