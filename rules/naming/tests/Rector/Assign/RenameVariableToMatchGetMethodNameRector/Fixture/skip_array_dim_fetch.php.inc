<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class ArrayDimFetchClass
{
    public function run()
    {
        $a[] = 1;
        $a[] = $this->getFastRunner();
        $a[1] = 'string';
    }
}

?>
