<?php

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\ProcessHelper;
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

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\ProcessHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

function stringToArgumentArray2()
{
    $procesHelper = new ProcessHelper();
    $output = new ConsoleOutput();

    $procesHelper->run($output, ['ls', '-l']);
}

?>
