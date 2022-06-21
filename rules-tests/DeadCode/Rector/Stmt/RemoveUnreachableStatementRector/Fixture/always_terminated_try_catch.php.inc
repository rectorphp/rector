<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class AlwaysTerminatedTryCatch
{
    public function run()
    {
        try {
            return something();
        } catch (Exception $e) {
            return null;
        }

        echo 'never executed';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

use Exception;

class AlwaysTerminatedTryCatch
{
    public function run()
    {
        try {
            return something();
        } catch (Exception $e) {
            return null;
        }
    }
}

?>
