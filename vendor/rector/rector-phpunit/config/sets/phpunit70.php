<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/phpunit-exception.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector::class)->configure([new \Rector\Renaming\ValueObject\RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $services->set(\Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector::class);
};
