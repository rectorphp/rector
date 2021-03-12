<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\FixturePhp74;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\SameNameMethod;

class ArrowFunction
{
    public function run(SameNameMethod $sameNameMethod)
    {
        $fn1 = fn($x) => $x + $stamp = $sameNameMethod->getName();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\FixturePhp74;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\SameNameMethod;

class ArrowFunction
{
    public function run(SameNameMethod $sameNameMethod)
    {
        $fn1 = fn($x) => $x + $fullName = $sameNameMethod->getName();
    }
}

?>
