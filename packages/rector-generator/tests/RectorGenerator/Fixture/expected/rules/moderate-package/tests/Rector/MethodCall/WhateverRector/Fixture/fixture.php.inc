<?php

namespace Rector\ModeratePackage\Tests\Rector\MethodCall\WhateverRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$key', 'value');
}

?>
-----
<?php

namespace Rector\ModeratePackage\Tests\Rector\MethodCall\WhateverRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value'
        ]]);
}

?>
