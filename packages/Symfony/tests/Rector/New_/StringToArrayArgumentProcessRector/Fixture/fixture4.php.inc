<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;

function stringToArgumentArray4()
{
    $process = new Process('git log --tags --simplify-by-decoration --pretty="format:%ai %d"');
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;

function stringToArgumentArray4()
{
    $process = new Process(['git', 'log', '--tags', '--simplify-by-decoration', '--pretty=format:%ai %d']);
}

?>
