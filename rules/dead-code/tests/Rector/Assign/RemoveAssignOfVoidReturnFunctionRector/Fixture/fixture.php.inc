<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = $this->getOne();
    }

    private function getOne(): void
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $this->getOne();
    }

    private function getOne(): void
    {
    }
}

?>
