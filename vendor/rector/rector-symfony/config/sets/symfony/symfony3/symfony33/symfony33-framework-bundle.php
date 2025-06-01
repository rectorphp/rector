<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # framework bundle
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\AddConsoleCommandPass' => 'Symfony\\Component\\Console\\DependencyInjection\\AddConsoleCommandPass',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\SerializerPass' => 'Symfony\\Component\\Serializer\\DependencyInjection\\SerializerPass',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\FormPass' => 'Symfony\\Component\\Form\\DependencyInjection\\FormPass',
        'Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener' => 'Symfony\\Component\\HttpKernel\\EventListener\\SessionListener',
        'Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListenr' => 'Symfony\\Component\\HttpKernel\\EventListener\\TestSessionListener',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\ConfigCachePass' => 'Symfony\\Component\\Config\\DependencyInjection\\ConfigCachePass',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\PropertyInfoPass' => 'Symfony\\Component\\PropertyInfo\\DependencyInjection\\PropertyInfoPass',
    ]);
};
