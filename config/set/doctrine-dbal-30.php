<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/doctrine/dbal/blob/master/UPGRADE.md#bc-break-changes-in-handling-string-and-binary-columns
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'DBAL\Platforms\AbstractPlatform' => [
                    'getVarcharTypeDeclarationSQL' => 'getStringTypeDeclarationSQL',
                ],
                'Doctrine\DBAL\Driver\DriverException' => [
                    'getErrorCode' => 'getCode',
                ],
            ],
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Doctrine\DBAL\Connection' => [
                    'ping' => 'void',
                ],
            ],
        ]]);
};
