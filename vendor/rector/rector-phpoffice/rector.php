<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::NAMING);
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class => [__DIR__ . '/config']]);
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
};
