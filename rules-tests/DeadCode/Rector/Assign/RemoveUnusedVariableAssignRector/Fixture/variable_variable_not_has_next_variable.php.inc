<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class VariableVariableNotHasNextVariable
{
    public function run($abcdef = 'foo')
    {
        $$abcdef = 'test';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class VariableVariableNotHasNextVariable
{
    public function run($abcdef = 'foo')
    {
    }
}

?>