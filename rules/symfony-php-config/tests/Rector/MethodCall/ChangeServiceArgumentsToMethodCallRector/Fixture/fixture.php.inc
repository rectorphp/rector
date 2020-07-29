<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}

?>
-----
<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value
        ]]);
}

?>
