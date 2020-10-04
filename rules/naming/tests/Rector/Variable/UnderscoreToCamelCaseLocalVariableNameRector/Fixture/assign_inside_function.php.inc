<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

function run()
{
    $_a = 'a';
    $_some_value = $_a;
    (function ($_some_value) use ($_a) {
        $_some_value;
        $_a;
    });
}
-----
<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

function run()
{
    $a = 'a';
    $someValue = $a;
    (function ($someValue) use ($a) {
        $someValue;
        $a;
    });
}
