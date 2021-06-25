<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class OnObGetClean
{
    public function run()
    {
        echo 'run';
    }

    public function execute()
    {
        ob_start();

        $this->run();

        $result = ob_get_clean();

        echo 'done';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class OnObGetClean
{
    public function run()
    {
        echo 'run';
    }

    public function execute()
    {
        ob_start();

        $this->run();

        ob_get_clean();

        echo 'done';
    }
}

?>
