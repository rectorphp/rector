<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SomePropertyFetch
{
    public function run()
    {
        $array = [];
        foreach ($this->variables as $property) {
            $array[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SomePropertyFetch
{
    public function run()
    {
        $array = [];
        foreach ($this->variables as $variable) {
            $array[] = $variable;
        }
    }
}

?>
