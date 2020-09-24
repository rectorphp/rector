<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class FirstUnderscore
{
    public function run($_a)
    {
        $_some_value = $_a;
    }
}
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class FirstUnderscore
{
    public function run($_a)
    {
        $someValue = $_a;
    }
}
