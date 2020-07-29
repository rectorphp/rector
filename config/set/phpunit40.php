<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            '$oldToNewMethodsByClass' => [
                'PHPUnit_Framework_MockObject_MockObject' => [
                    # see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/137
                    'staticExpects' => 'expects',
                ],
            ],
        ]]);
};
