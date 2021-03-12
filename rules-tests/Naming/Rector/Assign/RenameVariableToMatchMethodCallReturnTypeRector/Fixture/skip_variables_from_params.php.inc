<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

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
