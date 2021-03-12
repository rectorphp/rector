<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipOnConflict
{
    public function run()
    {
        $a = $this->getFastRunner();
        $a->exit();

        $fastRunner = 2;
    }
}

?>
