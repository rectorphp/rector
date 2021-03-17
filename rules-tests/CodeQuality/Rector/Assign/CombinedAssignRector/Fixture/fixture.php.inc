<?php

function combinedAssignFunction()
{
    $value = 10;
    $value = $value + 2;

    $foo = 1;
    $foo = $foo . '';

    $bar = 0;
    $bar = $bar + 2.000;

    $baz = 1000;
    $baz = $baz ** 2;
    $quux = $quux | Something::FOO;
}

?>
-----
<?php

function combinedAssignFunction()
{
    $value = 10;
    $value += 2;

    $foo = 1;
    $foo .= '';

    $bar = 0;
    $bar += 2.000;

    $baz = 1000;
    $baz **= 2;
    $quux |= Something::FOO;
}

?>
