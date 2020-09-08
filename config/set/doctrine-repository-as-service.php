<?php

declare(strict_types=1);

use Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector;
use Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Rector\Generic\Rector\Class_\RemoveParentRector;
use Rector\Generic\Rector\ClassLike\RemoveAnnotationRector;
use Rector\Generic\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Generic\ValueObject\ParentDependency;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Rector\Transform\ValueObject\ParentCallToProperty;
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
    $services->set(ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector::class);

    $services->set(RemoveAnnotationRector::class)
        ->call('configure', [[
            RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => ['method'],
        ]]);

    $services->set(AddPropertyByParentRector::class)
        ->call('configure', [[
            AddPropertyByParentRector::PARENT_DEPENDENCIES => inline_value_objects([
                new ParentDependency(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'Doctrine\ORM\EntityManagerInterface'
                ),
            ]),
        ]]);

    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->call('configure', [[
            ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => inline_value_objects([
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'createQueryBuilder',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'createResultSetMappingBuilder',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'clear',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'find',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'findBy',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'findAll',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'count',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'getClassName',
                    'entityRepository'
                ),
                new ParentCallToProperty(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'matching',
                    'entityRepository'
                ),
            ]),
        ]]
    );

    $services->set(MethodCallToPropertyFetchRector::class)
        ->call('configure', [[
            MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
                'getEntityManager' => 'entityManager',
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
