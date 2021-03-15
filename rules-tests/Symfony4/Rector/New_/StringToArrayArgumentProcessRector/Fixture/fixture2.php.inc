<?php

namespace Rector\Tests\Symfony4\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

function stringToArgumentArray2()
{
    $procesHelper = new ProcessHelper();
    $output = new ConsoleOutput();

    $procesHelper->run($output, 'ls -l');
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

function stringToArgumentArray2()
{
    $procesHelper = new ProcessHelper();
    $output = new ConsoleOutput();

    $procesHelper->run($output, ['ls', '-l']);
}

?>
