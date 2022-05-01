<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->parallel();
    $rectorConfig->skip(['*/Fixture/*', '*/Source/*', '*/Source*/*', '*/tests/*/Fixture*/Expected/*', \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class => [__DIR__ . '/config']]);
    $rectorConfig->ruleWithConfiguration(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class, ['Symfony\\*', 'Twig_*', 'Swift_*', 'Doctrine\\*']);
    // for testing
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $rectorConfig->sets([\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81, \Rector\Set\ValueObject\SetList::CODE_QUALITY, \Rector\Set\ValueObject\SetList::DEAD_CODE, \Rector\Set\ValueObject\SetList::NAMING, \Rector\Symfony\Set\SymfonySetList::SYMFONY_60]);
};
