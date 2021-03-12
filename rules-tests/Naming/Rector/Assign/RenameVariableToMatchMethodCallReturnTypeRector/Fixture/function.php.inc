<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner;

function run()
{
    $a = getFastRunner();
    $a->exit();
}

/**
 * @return FastRunner
 */
function getFastRunner()
{
    return new FastRunner();
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner;

function run()
{
    $fastRunner = getFastRunner();
    $fastRunner->exit();
}

/**
 * @return FastRunner
 */
function getFastRunner()
{
    return new FastRunner();
}

?>
