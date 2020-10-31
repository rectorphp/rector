<?php

declare(strict_types=1);

use PHPStan\Type\ObjectType;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\ValueObject\ArgumentRemover;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([
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
            ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects([
                new ArgumentRemover(
                    'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister',
                    'getSelectJoinColumnSQL',
                    4,
                    null
                ),
            ]),
        ]]);
};
