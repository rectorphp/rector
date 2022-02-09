<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\DeadCode\Rector\ClassLike\RemoveAnnotationRector;
use Rector\Doctrine\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector;
use Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
/**
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 * @see https://tomasvotruba.com/blog/2018/04/02/rectify-turn-repositories-to-services-in-symfony/
 * @see https://getrector.org/blog/2021/02/08/how-to-instantly-decouple-symfony-doctrine-repository-inheritance-to-clean-composition
 */
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # order matters, this needs to be first to correctly detect parent repository
    // covers "extends EntityRepository"
    $services->set(\Rector\Doctrine\Rector\Class_\MoveRepositoryFromParentToConstructorRector::class);
    $services->set(\Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
    $services->set(\Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector::class);
    // covers "extends ServiceEntityRepository"
    // @see https://github.com/doctrine/DoctrineBundle/pull/727/files
    $services->set(\Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector::class);
    $services->set(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class)->configure([new \Rector\Renaming\ValueObject\RenameProperty('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', '_em', 'entityManager')]);
    $services->set(\Rector\DeadCode\Rector\ClassLike\RemoveAnnotationRector::class)->configure(['method']);
    $services->set(\Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector::class)->configure([new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'createQueryBuilder', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'createResultSetMappingBuilder', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'clear', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'find', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'findBy', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'findAll', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'count', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'getClassName', 'entityRepository'), new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository', 'matching', 'entityRepository')]);
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector::class)->configure(['getEntityManager' => 'entityManager']);
    $services->set(\Rector\Removing\Rector\Class_\RemoveParentRector::class)->configure(['Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository']);
};
