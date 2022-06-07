<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // Rename is now move, specific for files.
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'rename', 'move'),
        // No arbitrary abbreviations
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'createDir', 'createDirectory'),
        // Writes are now deterministic
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'update', 'write'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'updateStream', 'writeStream'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'put', 'write'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'putStream', 'writeStream'),
        // Metadata getters are renamed
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'getTimestamp', 'lastModified'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'has', 'fileExists'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'getMimetype', 'mimeType'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'getSize', 'fileSize'),
        new MethodCallRename('RectorPrefix20220607\\League\\Flysystem\\FilesystemInterface', 'getVisibility', 'visibility'),
    ]);
};
