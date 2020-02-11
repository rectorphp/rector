<?php

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderInstance()
{
    $process = ProcessBuilder::create('something')->getProcess();
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderInstance()
{
    $process = (new ProcessBuilder('something'))->getProcess();
}

?>
