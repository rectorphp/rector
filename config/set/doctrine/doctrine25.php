<?php

declare(strict_types=1);

use Rector\Core\Rector\Argument\ArgumentRemoverRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->arg('$typehintForParameterByMethodByClass', [
            'Doctrine\ORM\Mapping\ClassMetadataFactory' => [
                'setEntityManager' => ['Doctrine\ORM\EntityManagerInterface'],
            ],
            'Doctrine\ORM\Tools\DebugUnitOfWorkListener' => [
                'dumpIdentityMap' => ['Doctrine\ORM\EntityManagerInterface'],
            ],
        ]);

    $services->set(ArgumentRemoverRector::class)
        ->arg('$positionsByMethodNameByClassType', [
            'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister' => [
                'getSelectJoinColumnSQL' => [
                    4 => null,
                ],
            ],
        ]);
};
