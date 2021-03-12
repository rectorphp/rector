<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

function removeExtraParams()
{
    strlen('asdf', 1);
    strlen('asdf');

    functionWithVariadics(1);
    functionWithVariadics(1, 2);
}

function functionWithVariadics(...$variadic)
{
    $argumemnts = $variadic;
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

function removeExtraParams()
{
    strlen('asdf');
    strlen('asdf');

    functionWithVariadics(1);
    functionWithVariadics(1, 2);
}

function functionWithVariadics(...$variadic)
{
    $argumemnts = $variadic;
}

?>
