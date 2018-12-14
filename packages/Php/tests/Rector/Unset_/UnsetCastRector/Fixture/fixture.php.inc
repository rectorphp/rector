<?php

function unsetCast()
{
    $value = (unset)$value;

    (unset) strlen('hi');
}

?>
-----
<?php

function unsetCast()
{
    $value = null;

    null;
}

?>
