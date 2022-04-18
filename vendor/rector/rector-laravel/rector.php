<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        // skip for handle scoped, like in the rector-src as well
        // @see https://github.com/rectorphp/rector-src/blob/7f73cf017214257c170d34db3af7283eaeeab657/rector.php#L71
        \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class,
    ]);
    $rectorConfig->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::NAMING);
};
