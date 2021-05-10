<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(ArgumentRemoverRector::class)->call('configure', [[ArgumentRemoverRector::REMOVED_ARGUMENTS => ValueObjectInliner::inline([new ArgumentRemover('Symfony\\Component\\Yaml\\Yaml', 'parse', 2, ['Symfony\\Component\\Yaml\\Yaml::PARSE_KEYS_AS_STRINGS'])])]]);
    $services->set(MergeMethodAnnotationToRouteAnnotationRector::class);
};
