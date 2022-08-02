<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\BlameableBehaviorRector;
use Rector\Doctrine\Rector\Class_\LoggableBehaviorRector;
use Rector\Doctrine\Rector\Class_\SluggableBehaviorRector;
use Rector\Doctrine\Rector\Class_\SoftDeletableBehaviorRector;
use Rector\Doctrine\Rector\Class_\TimestampableBehaviorRector;
use Rector\Doctrine\Rector\Class_\TranslationBehaviorRector;
use Rector\Doctrine\Rector\Class_\TreeBehaviorRector;
# version gedmo/doctrine-extensions 2.x to knplabs/doctrine-behaviors 2.0
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TimestampableBehaviorRector::class);
    $rectorConfig->rule(SluggableBehaviorRector::class);
    $rectorConfig->rule(TreeBehaviorRector::class);
    $rectorConfig->rule(TranslationBehaviorRector::class);
    $rectorConfig->rule(SoftDeletableBehaviorRector::class);
    $rectorConfig->rule(BlameableBehaviorRector::class);
    $rectorConfig->rule(LoggableBehaviorRector::class);
};
