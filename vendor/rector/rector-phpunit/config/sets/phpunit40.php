<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename(
        'PHPUnit_Framework_MockObject_MockObject',
        # see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/137
        'staticExpects',
        'expects'
    )]);
};
