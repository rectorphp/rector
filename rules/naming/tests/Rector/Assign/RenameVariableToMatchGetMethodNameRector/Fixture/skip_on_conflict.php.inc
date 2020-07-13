<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

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
