<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class AssignMultiSameLine
{
    public function run()
    {
        $me = $this->createMe();
        $me = $this->createMe();
        $me = $this->createMe();

        return $me;
    }

    public function createMe()
    {
        return new self();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class AssignMultiSameLine
{
    public function run()
    {
        $this->createMe();
        $this->createMe();
        $me = $this->createMe();

        return $me;
    }

    public function createMe()
    {
        return new self();
    }
}

?>
