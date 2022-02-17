<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class AssignExprHasCallLike
{
    public function validate() {
       throw new Exception();
    }

    public function test($params)
    {
        $tmp = isset($params['test']) ? $this->validate($params['test']) : null;
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class AssignExprHasCallLike
{
    public function validate() {
       throw new Exception();
    }

    public function test($params)
    {
        isset($params['test']) ? $this->validate($params['test']) : null;
    }
}
?>
