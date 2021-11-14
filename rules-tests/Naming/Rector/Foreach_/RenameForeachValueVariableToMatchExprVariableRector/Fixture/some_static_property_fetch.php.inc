<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SomeStaticPropertyFetch
{
    public static $variables = ['test'];

    public function run()
    {
        $array = [];
        foreach ($this::$variables as $property) {
            $array[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SomeStaticPropertyFetch
{
    public static $variables = ['test'];

    public function run()
    {
        $array = [];
        foreach ($this::$variables as $variable) {
            $array[] = $variable;
        }
    }
}

?>
