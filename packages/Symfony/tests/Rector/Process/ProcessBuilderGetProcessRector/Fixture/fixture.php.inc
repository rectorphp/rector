<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector\Source\ProcessBuilder;

function processBuilderGetProcess()
{
    $builder = new ProcessBuilder([]);
    $process = $builder->getProcess();

    $commandLine = $builder->getProcess()->getCommandLine();
}

?>
-----
<?php

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector\Source\ProcessBuilder;

function processBuilderGetProcess()
{
    $builder = new ProcessBuilder([]);
    $process = $builder;

    $commandLine = $builder->getCommandLine();
}

?>
