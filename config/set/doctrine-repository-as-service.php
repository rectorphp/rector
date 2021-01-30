<?php

declare(strict_types=1);

use Rector\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector;
use Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Rector\Generic\ValueObject\AddPropertyByParent;
use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

/**
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 * @see https://tomasvotruba.com/blog/2018/04/02/rectify-turn-repositories-to-services-in-symfony/
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # order matters, this needs to be first to correctly detect parent repository

    // covers "extends EntityRepository"
    $services->set(MoveRepositoryFromParentToConstructorRector::class);
    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);

    // covers "extends ServiceEntityRepository"
    // @see https://github.com/doctrine/DoctrineBundle/pull/727/files
    $services->set(ServiceEntityRepositoryParentCallToDIRector::class);

    $services->set(RemoveAnnotationRector::class)
        ->call('configure', [[
            RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => ['method'],
        ]]);

    $services->set(AddPropertyByParentRector::class)
        ->call('configure', [[
            AddPropertyByParentRector::PARENT_DEPENDENCIES => ValueObjectInliner::inline([
                new AddPropertyByParent(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'Doctrine\ORM\EntityManagerInterface'
                ),
            ]),
        ]]);

    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->call('configure', [[
            ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => ValueObjectInliner::inline([
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'createQueryBuilder',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'createResultSetMappingBuilder',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'clear',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'find',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'findBy',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'findAll',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'count',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
                    'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository',
                    'getClassName',
                    'entityRepository'
                ),
                new ReplaceParentCallByPropertyCall(
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
