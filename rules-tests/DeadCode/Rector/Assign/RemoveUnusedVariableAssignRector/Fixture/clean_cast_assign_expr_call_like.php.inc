<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class CleanCastAssignExprCallLike
{
    public function run()
    {
        $response = (object) $this->api_post("whatever", []);

        return 5;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class CleanCastAssignExprCallLike
{
    public function run()
    {
        $this->api_post("whatever", []);

        return 5;
    }
}

?>
