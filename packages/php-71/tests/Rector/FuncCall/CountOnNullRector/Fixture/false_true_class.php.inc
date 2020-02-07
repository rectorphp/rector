<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use stdClass;

function countables2()
{
    $values = false;
    $count = count($values);

    $values = true;
    $count = count($values);

    $values = new stdClass();
    $count = count($values);
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use stdClass;

function countables2()
{
    $values = false;
    $count = is_array($values) || $values instanceof \Countable ? count($values) : 0;

    $values = true;
    $count = is_array($values) || $values instanceof \Countable ? count($values) : 0;

    $values = new stdClass();
    $count = is_array($values) || $values instanceof \Countable ? count($values) : 0;
}

?>
