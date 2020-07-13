<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

function run()
{
    $a = getFastRunner();
    $a->exit();
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

function run()
{
    $fastRunner = getFastRunner();
    $fastRunner->exit();
}

?>
