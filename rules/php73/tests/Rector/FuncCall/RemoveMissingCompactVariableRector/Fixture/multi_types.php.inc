<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

final class MultiTypes
{
    public function run()
    {
        $variables = ['foo', 'bar'];
        compact($variables);
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

final class MultiTypes
{
    public function run()
    {
        $variables = ['foo', 'bar'];
    }
}

?>
