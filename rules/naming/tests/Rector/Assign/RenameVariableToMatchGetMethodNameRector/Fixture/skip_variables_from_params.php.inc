<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

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
