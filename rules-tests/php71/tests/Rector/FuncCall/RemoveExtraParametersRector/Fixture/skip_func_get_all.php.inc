<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class SkipFuncGetAll
{
    public function run()
    {
        $this->perform();
        $this->perform(1);
        $this->perform(1, 2);
    }

    function perform()
    {
        $argumemnts = func_get_args();
        var_dump($argumemnts);
    }
}
