<?php

function elvis()
{
    $value = $a ? $a : false;
}

?>
-----
<?php

function elvis()
{
    $value = $a ?: false;
}

?>
