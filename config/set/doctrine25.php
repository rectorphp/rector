<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\ValueObject\RemovedArgument;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\ParameterTypehint;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([
                new ParameterTypehint(
                    'Doctrine\ORM\Mapping\ClassMetadataFactory',
                    'setEntityManager',
                    0,
                    'Doctrine\ORM\EntityManagerInterface'
                ),
                new ParameterTypehint(
                    'Doctrine\ORM\Tools\DebugUnitOfWorkListener',
                    'dumpIdentityMap',
                    0,
                    'Doctrine\ORM\EntityManagerInterface'
                ),
            ]),
        ]]);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects([
                new RemovedArgument(
                    'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister',
                    'getSelectJoinColumnSQL',
                    4,
                    null
                ),
            ]),
        ]]);
};
