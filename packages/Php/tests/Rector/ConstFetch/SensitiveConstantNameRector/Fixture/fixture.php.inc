<?php

function sensitiveConstantName()
{
    define('FOO', 42, true);

    FOO;
    var_dump(FOO);

    foo;
    var_dump(foo);
}

?>
-----
<?php

function sensitiveConstantName()
{
    define('FOO', 42, true);

    FOO;
    var_dump(FOO);

    \FOO;
    var_dump(\FOO);
}

?>
