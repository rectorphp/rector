<?php

function simplifyFuncGetArgs()
{
    if (count(func_get_args()) === 5) {
        echo 'yes!';
    }
}

?>
-----
<?php

function simplifyFuncGetArgs()
{
    if (func_num_args() === 5) {
        echo 'yes!';
    }
}

?>
