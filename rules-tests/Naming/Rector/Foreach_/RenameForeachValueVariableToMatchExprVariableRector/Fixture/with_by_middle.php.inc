<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddle
{
    public function run()
    {
        $array = [];
        foreach ($methodsByClass as $property) {
            $array[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddle
{
    public function run()
    {
        $array = [];
        foreach ($methodsByClass as $methods) {
            $array[] = $methods;
        }
    }
}

?>
