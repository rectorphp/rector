<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipOnConflictWithParamNameClass
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
