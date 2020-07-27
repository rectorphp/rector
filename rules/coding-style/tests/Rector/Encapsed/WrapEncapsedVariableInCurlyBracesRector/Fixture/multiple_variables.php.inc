<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function multiple_variables($hello, $world)
{
    return "$hello $world!";
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function multiple_variables($hello, $world)
{
    return "{$hello} {$world}!";
}

?>
