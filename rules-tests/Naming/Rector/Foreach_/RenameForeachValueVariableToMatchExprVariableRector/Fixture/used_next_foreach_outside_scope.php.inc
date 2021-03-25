<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class UsedNextForeachOutsideScope
{
    public function run()
    {
        function () {
            $array = [];
            foreach ($variables as $property) {
                $array[] = $property;
            }
        };

        echo $variable;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class UsedNextForeachOutsideScope
{
    public function run()
    {
        function () {
            $array = [];
            foreach ($variables as $variable) {
                $array[] = $variable;
            }
        };

        echo $variable;
    }
}

?>
