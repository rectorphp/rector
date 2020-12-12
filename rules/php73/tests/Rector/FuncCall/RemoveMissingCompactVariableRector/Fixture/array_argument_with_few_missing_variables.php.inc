<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class FewMissingVariables
{
    public function run()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo', 'bar'], ['baz', 'qax']);
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class FewMissingVariables
{
    public function run()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo', 'bar']);
    }
}

?>
