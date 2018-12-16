<?php

function removeExtraParams()
{
    strlen('asdf', 1);
    strlen('asdf');

    functionWithParameters(1);
    functionWithParameters(1, 2);
    functionWithParameters(1, 2, 3);

    functionWithVariadics(1);
    functionWithVariadics(1, 2);
}

function functionWithParameters($a, $b = 5)
{
}

function functionWithVariadics(...$variadic)
{
    $argumemnts = $variadic;
}

?>
-----
<?php

function removeExtraParams()
{
    strlen('asdf');
    strlen('asdf');

    functionWithParameters(1);
    functionWithParameters(1, 2);
    functionWithParameters(1, 2);

    functionWithVariadics(1);
    functionWithVariadics(1, 2);
}

function functionWithParameters($a, $b = 5)
{
}

function functionWithVariadics(...$variadic)
{
    $argumemnts = $variadic;
}

?>
