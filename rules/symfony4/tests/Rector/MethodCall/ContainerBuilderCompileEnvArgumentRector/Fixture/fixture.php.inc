<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector\Fixture;

function containerBuilderCompilerEnvArgument()
{
    $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $containerBuilder->compile();
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector\Fixture;

function containerBuilderCompilerEnvArgument()
{
    $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $containerBuilder->compile(true);
}

?>
