<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddProphecyTraitRector::class);

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'PHPUnit\Framework\TestCase' => [
                'assertFileNotExists' => 'assertFileDoesNotExist',
            ],
        ]);
};
