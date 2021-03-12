<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class AssignNotFromParameter
{
    public function run()
    {
        $_a = 'a';
        $_some_value = $_a;
        $this->run($_some_value);
        $this->run(function ($_some_value) use ($_a) {
            $_some_value;
            $_a;
        });
    }
}
-----
<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class AssignNotFromParameter
{
    public function run()
    {
        $a = 'a';
        $someValue = $a;
        $this->run($someValue);
        $this->run(function ($someValue) use ($a) {
            $someValue;
            $a;
        });
    }
}
