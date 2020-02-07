<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

function countables($values)
{
    $valueCount = is_array($values) || $values instanceof Countable ? count($values) : 0;
    $valueCount = count($values);

    if (count($values) >= 2) {
        echo 'yes';
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

function countables($values)
{
    $valueCount = is_array($values) || $values instanceof Countable ? count($values) : 0;
    $valueCount = is_array($values) || $values instanceof \Countable ? count($values) : 0;

    if ((is_array($values) || $values instanceof \Countable ? count($values) : 0) >= 2) {
        echo 'yes';
    }
}

?>
