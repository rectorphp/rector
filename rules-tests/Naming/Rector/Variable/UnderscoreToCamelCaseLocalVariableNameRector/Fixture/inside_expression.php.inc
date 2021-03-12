<?php

namespace Rector\Tests\Naming\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class InsideExpression
{
    public function run($_a)
    {
        if ($_a) {
            $_some_value = $_a;
        }
    }
}
-----
<?php

namespace Rector\Tests\Naming\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class InsideExpression
{
    public function run($_a)
    {
        if ($_a) {
            $someValue = $_a;
        }
    }
}
