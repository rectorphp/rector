<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Process\Process;

function stringToArgumentArray4()
{
    $process = new Process('git log --tags --simplify-by-decoration --pretty="format:%ai %d"');
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Process\Process;

function stringToArgumentArray4()
{
    $process = new Process(['git', 'log', '--tags', '--simplify-by-decoration', '--pretty=format:%ai %d']);
}

?>
