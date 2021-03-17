<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddleMulti
{
    public function run()
    {
        $array = [];
        foreach ($nodesByTypeAndPosition as $property) {
            $array[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddleMulti
{
    public function run()
    {
        $array = [];
        foreach ($nodesByTypeAndPosition as $nodes) {
            $array[] = $nodes;
        }
    }
}

?>
