<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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
