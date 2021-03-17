<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipOnConflictWithParamName
{
    public function run($fastRunner)
    {
        if ($a instanceof SlowRunner) {
            $a = $this->getFastRunner();
            $a->exit();
        }
    }
}

?>
