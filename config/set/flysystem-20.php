<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([
        // Rename is now move, specific for files.
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'rename', 'move'),
        // No arbitrary abbreviations
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'createDir', 'createDirectory'),
        // Writes are now deterministic
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'update', 'write'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'updateStream', 'writeStream'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'put', 'write'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'putStream', 'writeStream'),
        // Metadata getters are renamed
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'getTimestamp', 'lastModified'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'has', 'fileExists'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'getMimetype', 'mimeType'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'getSize', 'fileSize'),
        new \Rector\Renaming\ValueObject\MethodCallRename('League\\Flysystem\\FilesystemInterface', 'getVisibility', 'visibility'),
    ]);
};
