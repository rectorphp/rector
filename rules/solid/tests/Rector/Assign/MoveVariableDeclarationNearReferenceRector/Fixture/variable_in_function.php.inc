<?php

function variableInFunction()
{
    $var = 1;
    if (mktime() === false) {
        return $var;
    }
}

?>
-----
<?php

function variableInFunction()
{
    if (mktime() === false) {
        $var = 1;
        return $var;
    }
}

?>
