<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\DifferentClass;
use Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameClassConstFetchRector::class)
        ->configure([
            new RenameClassConstFetch(LocalFormEvents::class, 'PRE_BIND', 'PRE_SUBMIT'),
            new RenameClassConstFetch(LocalFormEvents::class, 'BIND', 'SUBMIT'),
            new RenameClassConstFetch(LocalFormEvents::class, 'POST_BIND', 'POST_SUBMIT'),
            new RenameClassAndConstFetch(LocalFormEvents::class, 'OLD_CONSTANT', DifferentClass::class, 'NEW_CONSTANT'),
        ]);
};
