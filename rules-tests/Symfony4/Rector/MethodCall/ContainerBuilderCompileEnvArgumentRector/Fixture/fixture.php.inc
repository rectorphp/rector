<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector\Fixture;

function containerBuilderCompilerEnvArgument()
{
    $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $containerBuilder->compile();
}

?>
-----
<?php

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector\Fixture;

function containerBuilderCompilerEnvArgument()
{
    $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $containerBuilder->compile(true);
}

?>
