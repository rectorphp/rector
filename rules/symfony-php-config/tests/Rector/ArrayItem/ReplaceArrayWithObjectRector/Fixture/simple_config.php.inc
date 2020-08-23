<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ReplaceArrayWithObjectRector\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Auth\Access\Gate' => [
                    'access' => 'inspect',
                ]
            ]]
        ]);
}

?>
-----
<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ReplaceArrayWithObjectRector\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => \Rector\SymfonyPhpConfig\inline_value_objects([
                new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\Auth\Access\Gate', 'access', 'inspect')
            ])]
        ]);
}

?>
