<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Source\FastRunner;

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

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Source\FastRunner;

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
