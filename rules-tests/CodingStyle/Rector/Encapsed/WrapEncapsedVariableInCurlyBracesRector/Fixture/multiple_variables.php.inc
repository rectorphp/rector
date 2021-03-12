<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function multiple_variables($hello, $world)
{
    return "$hello $world!";
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function multiple_variables($hello, $world)
{
    return "{$hello} {$world}!";
}

?>
