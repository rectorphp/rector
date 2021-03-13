<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SinglePrefix
{
    public function run()
    {
        $array = [];
        foreach ($variable as $property) {
            $array[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SinglePrefix
{
    public function run()
    {
        $array = [];
        foreach ($variable as $singleVariable) {
            $array[] = $singleVariable;
        }
    }
}

?>
