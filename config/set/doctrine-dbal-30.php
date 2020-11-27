<?php

declare(strict_types=1);

use PHPStan\Type\VoidType;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# https://github.com/doctrine/dbal/blob/master/UPGRADE.md#bc-break-changes-in-handling-string-and-binary-columns
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename(
                    'Doctrine\DBAL\Platforms\AbstractPlatform',
                    'getVarcharTypeDeclarationSQL',
                    'getStringTypeDeclarationSQL'
                ),
                new MethodCallRename('Doctrine\DBAL\Driver\DriverException', 'getErrorCode', 'getCode'),
            ]),
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
                new AddReturnTypeDeclaration('Doctrine\DBAL\Connection', 'ping', new VoidType()),
            ]),
        ]]);

    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-abstractionresult
    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Doctrine\DBAL\Abstraction\Result' => 'Doctrine\DBAL\Result',
            ],
        ]]);
};
