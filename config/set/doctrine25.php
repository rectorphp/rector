<?php

declare(strict_types=1);

use PHPStan\Type\ObjectType;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'Doctrine\ORM\Mapping\ClassMetadataFactory',
                    'setEntityManager',
                    0,
                    new ObjectType('Doctrine\ORM\EntityManagerInterface')
                ),
                new AddParamTypeDeclaration(
                    'Doctrine\ORM\Tools\DebugUnitOfWorkListener',
                    'dumpIdentityMap',
                    0,
                    new ObjectType('Doctrine\ORM\EntityManagerInterface')
                ),
            ]),
        ]]);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::REMOVED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentRemover(
                    'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister',
                    'getSelectJoinColumnSQL',
                    4,
                    null
                ),
            ]),
        ]]);
};
