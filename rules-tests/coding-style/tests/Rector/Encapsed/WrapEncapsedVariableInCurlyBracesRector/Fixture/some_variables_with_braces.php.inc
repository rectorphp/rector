<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function some_variales_with_braces($hello, $world)
{
    return "$hello {$world}!";
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function some_variales_with_braces($hello, $world)
{
    return "{$hello} {$world}!";
}

?>
