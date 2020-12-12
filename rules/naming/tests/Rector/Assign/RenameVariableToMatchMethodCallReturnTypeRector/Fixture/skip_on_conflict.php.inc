<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipOnConflictClass
{
    public function run()
    {
        $a = $this->getFastRunner();
        $a->exit();

        $fastRunner = 2;
    }
}

?>
