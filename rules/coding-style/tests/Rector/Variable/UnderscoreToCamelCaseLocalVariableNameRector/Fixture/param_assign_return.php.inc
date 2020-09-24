<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class ParamAssignReturn
{
    public function run($_a)
    {
        $_some_value = $_a;
        return $_some_value;
    }
}
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class ParamAssignReturn
{
    public function run($_a)
    {
        $someValue = $_a;
        return $someValue;
    }
}
