<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipNoGet
{
    public function run()
    {
        $a = $this->fastRunner();
    }
}

?>
