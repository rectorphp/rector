<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddleMulti
{
    public function run()
    {
        $array = [];
        foreach ($nodesByTypeAndPosition as $node) {
            $array[] = $node;
        }
    }
}

?>
