<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class)->call('configure', [[
        \Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Renaming\ValueObject\RenameClassConstFetch(
                \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents::class,
                'PRE_BIND',
                'PRE_SUBMIT'
            ),
            new \Rector\Renaming\ValueObject\RenameClassConstFetch(
                \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents::class,
                'BIND',
                'SUBMIT'
            ),
            new \Rector\Renaming\ValueObject\RenameClassConstFetch(
                \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents::class,
                'POST_BIND',
                'POST_SUBMIT'
            ),
            new \Rector\Renaming\ValueObject\RenameClassAndConstFetch(
                \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents::class,
                'OLD_CONSTANT',
                \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\DifferentClass::class,
                'NEW_CONSTANT'
            ),
























            
        ]),
    ]]);
};
