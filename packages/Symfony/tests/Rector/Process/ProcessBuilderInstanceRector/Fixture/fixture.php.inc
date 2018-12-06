<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\Source\ProcessBuilder;

function processBuilderInstance()
{
    $process = ProcessBuilder::create('something')->getProcess();
}

?>
-----
<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\Source\ProcessBuilder;

function processBuilderInstance()
{
    $process = (new ProcessBuilder('something'))->getProcess();
}

?>
