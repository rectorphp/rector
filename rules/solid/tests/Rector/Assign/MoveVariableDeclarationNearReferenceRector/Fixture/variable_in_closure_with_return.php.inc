<?php

function variableInClosureWithReturn()
{
    return function () {
        $var = 1;
        if (mktime() === false) {
            return $var;
        }
    };
}

?>
-----
<?php

function variableInClosureWithReturn()
{
    return function () {
        if (mktime() === false) {
            $var = 1;
            return $var;
        }
    };
}

?>
