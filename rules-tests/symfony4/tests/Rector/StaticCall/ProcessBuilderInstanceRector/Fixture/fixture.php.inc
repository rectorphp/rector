<?php

namespace Rector\Symfony4\Tests\Rector\StaticCall\ProcessBuilderInstanceRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderInstance()
{
    $process = ProcessBuilder::create('something')->getProcess();
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\StaticCall\ProcessBuilderInstanceRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderInstance()
{
    $process = (new ProcessBuilder('something'))->getProcess();
}

?>
