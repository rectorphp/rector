<?php

function variableWithUsageInMultipleLevels()
{
    $var = 1;
    if (mktime() === false) {
        return $var;
    }
    echo $var;
}

?>
-----
<?php

function variableWithUsageInMultipleLevels()
{
    $var = 1;
    if (mktime() === false) {
        return $var;
    }
    echo $var;
}

?>
