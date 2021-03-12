<?php

namespace Rector\Tests\Symfony4\Rector\StaticCall\ProcessBuilderInstanceRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderInstance()
{
    $process = ProcessBuilder::create('something')->getProcess();
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\StaticCall\ProcessBuilderInstanceRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderInstance()
{
    $process = (new ProcessBuilder('something'))->getProcess();
}

?>
