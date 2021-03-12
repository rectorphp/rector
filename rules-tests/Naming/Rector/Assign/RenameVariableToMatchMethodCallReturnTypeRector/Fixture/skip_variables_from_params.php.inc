<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipVariablesFromParams
{
    public function run($runner)
    {
        if ($runner instanceof SlowRunner) {
            $runner = $this->getFastRunner();
            $runner->exit();
        }
    }
}

?>
