<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipNoGet
{
    public function run()
    {
        $a = $this->fastRunner();
    }
}

?>
