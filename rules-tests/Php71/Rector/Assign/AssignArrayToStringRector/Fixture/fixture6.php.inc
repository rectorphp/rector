<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

function assignArrayToString6()
{
    $array = [1, 2, 3];

    $someString = '';
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

function assignArrayToString6()
{
    $array = [1, 2, 3];

    $someString = [];
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
