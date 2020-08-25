<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

function run()
{
    $foo = 'foo';
    $bar = 'bar';

    compact(['foo', 'bar'], ['baz']);
    compact(['foo'], ['bar', 'baz']);
    compact(['foo'], ['bar'], 'baz');
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

function run()
{
    $foo = 'foo';
    $bar = 'bar';

    compact(['foo', 'bar']);
    compact(['foo'], ['bar']);
    compact(['foo'], ['bar']);
}

?>
