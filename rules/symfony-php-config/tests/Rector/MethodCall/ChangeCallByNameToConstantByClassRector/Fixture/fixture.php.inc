<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Fixture;

use Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Source\IdClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(IdClass::class)
        ->call('configure', [[
            '$key' => 'value',
            '$keyLonger' => 'value2',
        ]]);
}

?>
-----
<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Fixture;

use Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Source\IdClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(IdClass::class)
        ->call('configure', [[
            \Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Source\IdClass::KEY => 'value',
            \Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\Source\IdClass::KEY_LONGER => 'value2',
        ]]);
}

?>
