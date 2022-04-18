<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    // experimental
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, ['*/Fixture/*', '*/Source/*', '*/Source*/*', '*/tests/*/Fixture*/Expected/*', \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class => [__DIR__ . '/config']]);
    $services = $rectorConfig->services();
    $services->set(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class)->configure(['Symfony\\*', 'Twig_*', 'Swift_*', 'Doctrine\\*']);
    $rectorConfig->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::NAMING);
    // for testing
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $rectorConfig->import(\Rector\Symfony\Set\SymfonySetList::SYMFONY_60);
};
