<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

function someFunction()
{
    $items = [1, 2, 3];
    end($items);
    $lastKey = key($items);
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArrayKeyFirstLastRector\Fixture;

function someFunction()
{
    $items = [1, 2, 3];
    $lastKey = array_key_last($items);
}

?>
