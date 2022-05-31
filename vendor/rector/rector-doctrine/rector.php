<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $rectorConfig->importNames();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->parallel();
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
    ]);
    $rectorConfig->sets([\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81, \Rector\Set\ValueObject\SetList::DEAD_CODE, \Rector\Set\ValueObject\SetList::CODE_QUALITY, \Rector\Set\ValueObject\SetList::NAMING]);
    $rectorConfig->ruleWithConfiguration(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class, ['Doctrine\\*', 'Gedmo\\*', 'Knp\\*', 'DateTime', 'DateTimeInterface']);
};
