<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/pull/29236
        'Symfony\\Component\\Cache\\Traits\\ApcuTrait\\ApcuCache' => 'Symfony\\Component\\Cache\\Traits\\ApcuTrait\\ApcuAdapter',
        'Symfony\\Component\\Cache\\Adapter\\SimpleCacheAdapter' => 'Symfony\\Component\\Cache\\Adapter\\Psr16Adapter',
        'Symfony\\Component\\Cache\\Simple\\ArrayCache' => 'Symfony\\Component\\Cache\\Adapter\\ArrayAdapter',
        'Symfony\\Component\\Cache\\Simple\\ChainCache' => 'Symfony\\Component\\Cache\\Adapter\\ChainAdapter',
        'Symfony\\Component\\Cache\\Simple\\DoctrineCache' => 'Symfony\\Component\\Cache\\Adapter\\DoctrineAdapter',
        'Symfony\\Component\\Cache\\Simple\\FilesystemCache' => 'Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter',
        'Symfony\\Component\\Cache\\Simple\\MemcachedCache' => 'Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter',
        'Symfony\\Component\\Cache\\Simple\\NullCache' => 'Symfony\\Component\\Cache\\Adapter\\NullAdapter',
        'Symfony\\Component\\Cache\\Simple\\PdoCache' => 'Symfony\\Component\\Cache\\Adapter\\PdoAdapter',
        'Symfony\\Component\\Cache\\Simple\\PhpArrayCache' => 'Symfony\\Component\\Cache\\Adapter\\PhpArrayAdapter',
        'Symfony\\Component\\Cache\\Simple\\PhpFilesCache' => 'Symfony\\Component\\Cache\\Adapter\\PhpFilesAdapter',
        'Symfony\\Component\\Cache\\Simple\\RedisCache' => 'Symfony\\Component\\Cache\\Adapter\\RedisAdapter',
        'Symfony\\Component\\Cache\\Simple\\TraceableCache' => 'Symfony\\Component\\Cache\\Adapter\\TraceableAdapterCache',
        'Symfony\\Component\\Cache\\Simple\\Psr6Cache' => 'Symfony\\Component\\Cache\\Psr16Cache',
    ]);
};
