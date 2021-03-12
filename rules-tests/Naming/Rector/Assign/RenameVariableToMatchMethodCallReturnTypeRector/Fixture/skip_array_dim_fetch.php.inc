<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

class SkipArrayDimFetch
{
    public function run()
    {
        $a[] = 1;
        $a[] = $this->getFastRunner();
        $a[1] = 'string';
    }
}

?>
