<?php
// rector.php

declare(strict_types=1);

use App\Rector\AddPropertyByParentRector;
use App\Rector\MethodCallRemoverRector;
use App\Rector\MethodCallToPropertyFetchRector;
use App\Rector\MoveClassesBySuffixRector;
use App\Rector\RemoveConstructorDependencyByParentRector;
use App\Rector\RemoveParentCallByParentRector;
use App\Rector\RemoveParentRector;
use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Rector\Core\Configuration\Option;
use Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Restoration\Rector\Class_\RemoveFinalFromEntityRector;
use Rector\Set\ValueObject\SetList;
use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Php74\Rector\Property\TypedPropertyRector;

return static function (ContainerConfigurator $containerConfigurator): void {

    $services = $containerConfigurator->services();
    $services
        ->set(AddPropertyByParentRector::class)
        ->call(
            'configure',
            [[
                '$parentsDependenciesToAdd' =>
                [
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository' => 'Doctrine\ORM\EntityManagerInterface'
                ]
            ]]
        );
    $services
        ->set(App\Rector\ReplaceParentCallByPropertyCallRector::class)
        ->call(
            'configure',
            [
                [
                    '$replaceParentCallByPropertyArguments' => [
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'createQueryBuilder', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'createResultSetMappingBuilder', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'clear', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'find', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'findBy', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'findAll', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'count', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'getClassName', 'entityRepository'],
                        ['Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', 'matching', 'entityRepository'],
                    ]
                ]
            ]
        );
    $services
        ->set(MethodCallToPropertyFetchRector::class)
        ->call(
            'configure',
            [[
                '$methodCallToPropertyFetchCollection' =>
                [
                    'getEntityManager' => 'entityManager'
                ]
            ]]
        );
    $services
        ->set(RemoveParentCallByParentRector::class)
        ->call(
            'configure',
            [[
                '$parentClasses' => [
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository'
                ]
            ]]
        );
    $services
        ->set(RemoveConstructorDependencyByParentRector::class)
        ->call(
            'configure',
            [[
                '$parentsDependenciesToRemove' =>
                [
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository' => 'Doctrine\Common\Persistence\ManagerRegistry'
                ]
            ]]
        );
    $services
        ->set(RemoveParentRector::class)
        ->call(
            'configure',
            [[
                '$parentsToRemove' =>
                [
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository'
                ]
            ]]
        );
    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);
};
