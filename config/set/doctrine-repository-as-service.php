<?php

declare(strict_types=1);

use Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Rector\Generic\Rector\Class_\RemoveParentRector;
use Rector\Generic\Rector\ClassMethod\RemoveConstructorDependencyByParentRector;
use Rector\Generic\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Rector\Generic\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Generic\Rector\StaticCall\RemoveParentCallByParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 * @see https://tomasvotruba.com/blog/2018/04/02/rectify-turn-repositories-to-services-in-symfony/
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # order matters, this needs to be first to correctly detect parent repository

    // covers "extends EntityRepository"
    $services->set(MoveRepositoryFromParentToConstructorRector::class);
    $services->set(ServiceLocatorToDIRector::class);
    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);

    // covers "extends ServiceEntityRepository"
    // @see https://github.com/doctrine/DoctrineBundle/pull/727/files
    $services->set(AddPropertyByParentRector::class)
        ->call('configure', [[
            AddPropertyByParentRector::PARENT_TYPES_TO_DEPENDENCIES => [
                'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository' => [
                    'Doctrine\ORM\EntityManagerInterface',
                ],
            ],
        ]]);

    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->call('configure', [[
            ReplaceParentCallByPropertyCallRector::PARENT_TYPE_TO_METHOD_NAME_TO_PROPERTY_FETCH => [
                'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository' => [
                    'createQueryBuilder' => 'entityRepository',
                    'createResultSetMappingBuilder' => 'entityRepository',
                    'clear' => 'entityRepository',
                    'find' => 'entityRepository',
                    'findBy' => 'entityRepository',
                    'findAll' => 'entityRepository',
                    'count' => 'entityRepository',
                    'getClassName' => 'entityRepository',
                    'matching' => 'entityRepository',
                ],
            ],
        ]]
    );

    $services->set(MethodCallToPropertyFetchRector::class)
        ->call('configure', [[
            MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
                'getEntityManager' => 'entityManager',
            ],
        ]]);

    $services->set(RemoveParentCallByParentRector::class)
        ->call('configure', [[
            RemoveParentCallByParentRector::PARENT_CLASSES => [
                'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
            ],
        ]]
        );

    $services->set(RemoveConstructorDependencyByParentRector::class)
        ->call('configure', [[
            RemoveConstructorDependencyByParentRector::PARENT_TYPE_TO_PARAM_TYPES_TO_REMOVE => [
                'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository' => [
                    'Doctrine\Common\Persistence\ManagerRegistry',
                ],
            ],
        ]]);

    $services->set(RemoveParentRector::class)
        ->call('configure', [[
            RemoveParentRector::PARENT_TYPES_TO_REMOVE => [
                'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
            ],
        ]]);

    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);

    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
};
