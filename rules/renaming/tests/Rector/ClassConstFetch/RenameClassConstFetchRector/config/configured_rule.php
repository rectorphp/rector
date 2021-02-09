<?php

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\DifferentClass;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameClassConstFetchRector::class)->call('configure', [[
        RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([

            new RenameClassConstFetch(LocalFormEvents::class, 'PRE_BIND', 'PRE_SUBMIT'),
            new RenameClassConstFetch(LocalFormEvents::class, 'BIND', 'SUBMIT'),
            new RenameClassConstFetch(LocalFormEvents::class, 'POST_BIND', 'POST_SUBMIT'),
            new RenameClassAndConstFetch(
                LocalFormEvents::class,
                'OLD_CONSTANT',
                DifferentClass::class,
                'NEW_CONSTANT'
            ),

        ]),
    ]]);
};
