<?php

namespace Rector\Php73\Tests\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

function process() {
    $items = [1, 2, 3];
    reset($items);
    $firstKey = key($items);

    reset($items);
    $firstKey = key($differntItems);
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

function process() {
    $items = [1, 2, 3];
    $firstKey = array_key_first($items);

    reset($items);
    $firstKey = key($differntItems);
}

?>
