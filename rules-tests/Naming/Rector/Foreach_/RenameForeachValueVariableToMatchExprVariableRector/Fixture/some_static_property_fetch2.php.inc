<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SomeStaticPropertyFetch2
{
    public static $variables = ['test'];

    public function run()
    {
        $array = [];
        foreach (self::$variables as $property) {
            $array[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

final class SomeStaticPropertyFetch2
{
    public static $variables = ['test'];

    public function run()
    {
        $array = [];
        foreach (self::$variables as $variable) {
            $array[] = $variable;
        }
    }
}

?>
