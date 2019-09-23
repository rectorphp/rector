<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class EmptyCompact
{
    public function run()
    {
        return compact('non_existing');
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class EmptyCompact
{
    public function run()
    {
        return [];
    }
}

?>
