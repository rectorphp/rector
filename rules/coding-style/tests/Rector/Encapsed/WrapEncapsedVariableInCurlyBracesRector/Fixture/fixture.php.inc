<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function run($world)
{
    return "Hello $world!";
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function run($world)
{
    return "Hello {$world}!";
}

?>
