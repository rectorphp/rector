<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS => [
                'Doctrine\ORM\Mapping\ClassMetadataFactory' => [
                    'setEntityManager' => ['Doctrine\ORM\EntityManagerInterface'],
                ],
                'Doctrine\ORM\Tools\DebugUnitOfWorkListener' => [
                    'dumpIdentityMap' => ['Doctrine\ORM\EntityManagerInterface'],
                ],
            ],
        ]]);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE => [
                'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister' => [
                    'getSelectJoinColumnSQL' => [
                        4 => null,
                    ],
                ],
            ],
        ]]);
};
