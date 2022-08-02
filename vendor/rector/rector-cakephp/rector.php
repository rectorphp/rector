<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        StringClassNameToClassConstantRector::class => [__DIR__ . '/config'],
    ]);
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, SetList::DEAD_CODE, SetList::CODE_QUALITY, SetList::NAMING]);
};
