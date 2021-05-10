<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(AddProphecyTraitRector::class);
    $services->set(RenameMethodRector::class)->call('configure', [[RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([new MethodCallRename('PHPUnit\\Framework\\TestCase', 'assertFileNotExists', 'assertFileDoesNotExist')])]]);
};
