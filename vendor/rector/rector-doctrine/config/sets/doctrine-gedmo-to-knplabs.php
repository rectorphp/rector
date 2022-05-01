<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\BlameableBehaviorRector;
use Rector\Doctrine\Rector\Class_\LoggableBehaviorRector;
use Rector\Doctrine\Rector\Class_\SluggableBehaviorRector;
use Rector\Doctrine\Rector\Class_\SoftDeletableBehaviorRector;
use Rector\Doctrine\Rector\Class_\TimestampableBehaviorRector;
use Rector\Doctrine\Rector\Class_\TranslationBehaviorRector;
use Rector\Doctrine\Rector\Class_\TreeBehaviorRector;
# version gedmo/doctrine-extensions 2.x to knplabs/doctrine-behaviors 2.0
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\TimestampableBehaviorRector::class);
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\SluggableBehaviorRector::class);
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\TreeBehaviorRector::class);
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\TranslationBehaviorRector::class);
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\SoftDeletableBehaviorRector::class);
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\BlameableBehaviorRector::class);
    $rectorConfig->rule(\Rector\Doctrine\Rector\Class_\LoggableBehaviorRector::class);
};
