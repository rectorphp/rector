<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ProcessBuilderGetProcessRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderGetProcess()
{
    $builder = new ProcessBuilder([]);
    $process = $builder->getProcess();

    $commandLine = $builder->getProcess()->getCommandLine();
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ProcessBuilderGetProcessRector\Fixture;

use Symfony\Component\Process\ProcessBuilder;

function processBuilderGetProcess()
{
    $builder = new ProcessBuilder([]);
    $process = $builder;

    $commandLine = $builder->getCommandLine();
}

?>
