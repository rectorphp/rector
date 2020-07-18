<?php

declare(strict_types=1);

use Rector\DoctrineGedmoToKnplabs\Rector\Class_\BlameableBehaviorRector;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\TreeBehaviorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # version gedmo/doctrine-extensions 2.x to knplabs/doctrine-behaviors 2.0
    $services->set(TimestampableBehaviorRector::class);

    $services->set(SluggableBehaviorRector::class);

    $services->set(TreeBehaviorRector::class);

    $services->set(TranslationBehaviorRector::class);

    $services->set(SoftDeletableBehaviorRector::class);

    $services->set(BlameableBehaviorRector::class);

    $services->set(LoggableBehaviorRector::class);
};
