<?php

declare (strict_types=1);
namespace RectorPrefix20210514;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20210514\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\RectorPrefix20210514\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        // Rename is now move, specific for files.
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'rename', 'move'),
        // No arbitrary abbreviations
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'createDir', 'createDirectory'),
        // Writes are now deterministic
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'update', 'write'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'updateStream', 'writeStream'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'put', 'write'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'putStream', 'writeStream'),
        // Metadata getters are renamed
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'getTimestamp', 'lastModified'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'has', 'fileExists'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'getMimetype', 'mimeType'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'getSize', 'fileSize'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\Filesystem', 'getVisibility', 'visibility'),
    ])]]);
};
