<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                // Rename is now move, specific for files.
                new MethodCallRename('League\Flysystem\Filesystem', 'rename', 'move'),

                // No arbitrary abbreviations
                new MethodCallRename('League\Flysystem\Filesystem', 'createDir', 'createDirectory'),

                // Writes are now deterministic
                new MethodCallRename('League\Flysystem\Filesystem', 'update', 'write'),
                new MethodCallRename('League\Flysystem\Filesystem', 'updateStream', 'writeStream'),
                new MethodCallRename('League\Flysystem\Filesystem', 'put', 'write'),
                new MethodCallRename('League\Flysystem\Filesystem', 'putStream', 'writeStream'),

                // Metadata getters are renamed
                new MethodCallRename('League\Flysystem\Filesystem', 'getTimestamp', 'lastModified'),
                new MethodCallRename('League\Flysystem\Filesystem', 'has', 'fileExists'),
                new MethodCallRename('League\Flysystem\Filesystem', 'getMimetype', 'mimeType'),
                new MethodCallRename('League\Flysystem\Filesystem', 'getSize', 'fileSize'),
                new MethodCallRename('League\Flysystem\Filesystem', 'getVisibility', 'visibility'),
            ]),
        ]]);
};
