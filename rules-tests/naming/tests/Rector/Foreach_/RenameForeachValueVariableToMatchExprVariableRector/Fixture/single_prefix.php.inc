<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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
