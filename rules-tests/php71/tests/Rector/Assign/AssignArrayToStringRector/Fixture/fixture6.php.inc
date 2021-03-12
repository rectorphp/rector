<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

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

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

function assignArrayToString6()
{
    $array = [1, 2, 3];

    $someString = [];
    foreach ($array as $item) {
        $someString[] = $item;
    }
}

?>
