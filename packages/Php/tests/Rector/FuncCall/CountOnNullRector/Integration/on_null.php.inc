<?php

function countables()
{
    $values = null;
    $valueCount = is_array($values) || $values instanceof Countable ? count($values) : 0;
    $valueCount = count($values);

    if (count($values) >= 2) {
        echo 'yes';
    }
}

?>
-----
<?php

function countables()
{
    $values = null;
    $valueCount = is_array($values) || $values instanceof Countable ? count($values) : 0;
    $valueCount = is_array($values) || $values instanceof \Countable ? count($values) : 0;

    if ((is_array($values) || $values instanceof \Countable ? count($values) : 0) >= 2) {
        echo 'yes';
    }
}

?>
