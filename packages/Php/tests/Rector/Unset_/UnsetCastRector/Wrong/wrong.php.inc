<?php

function unsetCast()
{
    $value = (unset)$value;
}

?>
-----
<?php

function unsetCast()
{
    $value = null;
}

?>
