<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use RectorPrefix20220606\Rector\Removing\ValueObject\ArgumentRemover;
use RectorPrefix20220606\Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Symfony\\Component\\Yaml\\Yaml', 'parse', 2, ['Symfony\\Component\\Yaml\\Yaml::PARSE_KEYS_AS_STRINGS'])]);
    $rectorConfig->rule(MergeMethodAnnotationToRouteAnnotationRector::class);
};
