<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\BlameableBehaviorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\LoggableBehaviorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\SluggableBehaviorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\SoftDeletableBehaviorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\TimestampableBehaviorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\TranslationBehaviorRector;
use RectorPrefix20220606\Rector\Doctrine\Rector\Class_\TreeBehaviorRector;
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
