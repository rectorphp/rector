<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

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
