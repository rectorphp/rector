<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class RemoveAnotherClassMethodCallExtraArgument
{
    public function run()
    {
        $this->performBetter(1, 2);
    }

    function performBetter($value)
    {
        $argumemnts = better_func_get_args();
        var_dump($argumemnts);
    }
}

function better_func_get_args() {
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class RemoveAnotherClassMethodCallExtraArgument
{
    public function run()
    {
        $this->performBetter(1);
    }

    function performBetter($value)
    {
        $argumemnts = better_func_get_args();
        var_dump($argumemnts);
    }
}

function better_func_get_args() {
}

?>
