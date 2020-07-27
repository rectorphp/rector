<?php

declare(strict_types=1);

use Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/doctrine/dbal/blob/master/UPGRADE.md#bc-break-changes-in-handling-string-and-binary-columns
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'DBAL\Platforms\AbstractPlatform' => [
                'getVarcharTypeDeclarationSQL' => 'getStringTypeDeclarationSQL',
            ],
            'Doctrine\DBAL\Driver\DriverException' => [
                'getErrorCode' => 'getCode',
            ],
        ]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->arg('$typehintForMethodByClass', [
            'Doctrine\DBAL\Connection' => [
                'ping' => 'void',
            ],
        ]);
};
