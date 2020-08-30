<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipNoGetClass
{
    public function run()
    {
        $a = $this->fastRunner();
    }
}

?>
