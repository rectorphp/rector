<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class SkipNoGetClass
{
    public function run()
    {
        $a = $this->fastRunner();
    }
}

?>
