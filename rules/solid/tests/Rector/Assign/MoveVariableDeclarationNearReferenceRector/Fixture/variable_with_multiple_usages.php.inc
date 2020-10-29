<?php

function variableWithMultipleUsages()
{
    $var = 1;
    if (mktime() === false) {
        echo $var;
        return $var;
    }
}

?>
-----
<?php

function variableWithMultipleUsages()
{
    if (mktime() === false) {
        $var = 1;
        echo $var;
        return $var;
    }
}

?>
