<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

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

namespace Rector\Php\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class EmptyCompact
{
    public function run()
    {
        return [];
    }
}

?>
