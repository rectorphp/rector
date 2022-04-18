<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // for tests
        '*/Source/*',
        '*/Fixture/*',
    ]);
    $rectorConfig->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::NAMING);
    $services = $rectorConfig->services();
    $services->set(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class)->configure(['Doctrine\\*', 'Gedmo\\*', 'Knp\\*', 'DateTime', 'DateTimeInterface']);
};
