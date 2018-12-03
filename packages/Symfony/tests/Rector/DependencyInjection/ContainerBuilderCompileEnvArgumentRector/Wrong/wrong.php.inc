<?php

namespace Rector\Symfony\Tests\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector\Wrong;

function containerBuilderCompilerEnvArgument()
{
    $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $containerBuilder->compile();
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector\Wrong;

function containerBuilderCompilerEnvArgument()
{
    $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $containerBuilder->compile(true);
}

?>
