<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClassWithNewMethod;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassWithMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)
        ->configure([
            OldClassWithMethod::class => NewClassWithNewMethod::class,
        ]);

    $services->set(RenameMethodRector::class)
        ->configure([new MethodCallRename(NewClassWithNewMethod::class, 'someMethod', 'someNewMethod')]);
};
