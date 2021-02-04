<?php

declare(strict_types=1);

use PHPStan\Type\ObjectType;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Interop\Container\ContainerInterface' => 'Psr\Container\ContainerInterface',
            ],
        ]]);

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'Laminas\ServiceManager\Factory\AbstractFactoryInterface',
                    '__invoke',
                    0,
                    new ObjectType('Psr\Container\ContainerInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Laminas\ServiceManager\Factory\DelegatorFactoryInterface',
                    '__invoke',
                    0,
                    new ObjectType('Psr\Container\ContainerInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Laminas\ServiceManager\Factory\FactoryInterface',
                    '__invoke',
                    0,
                    new ObjectType('Psr\Container\ContainerInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Laminas\ServiceManager\Factory\InvokableFactory',
                    '__invoke',
                    0,
                    new ObjectType('Psr\Container\ContainerInterface'),
                ),
            ]),
        ]]);
};
