<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableNameRector\Fixture;

final class FirstUnderscore
{
    public function run($_a)
    {
        $_some_value = $_a;
    }
}
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableNameRector\Fixture;

final class FirstUnderscore
{
    public function run($a)
    {
        $someValue = $a;
    }
}
