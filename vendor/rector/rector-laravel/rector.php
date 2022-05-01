<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\DogFood\Rector\Closure\UpgradeRectorConfigRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        // skip for handle scoped, like in the rector-src as well
        // @see https://github.com/rectorphp/rector-src/blob/7f73cf017214257c170d34db3af7283eaeeab657/rector.php#L71
        \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class,
    ]);
    $rectorConfig->sets([\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81, \Rector\Set\ValueObject\SetList::DEAD_CODE, \Rector\Set\ValueObject\SetList::CODE_QUALITY, \Rector\Set\ValueObject\SetList::NAMING]);
};
