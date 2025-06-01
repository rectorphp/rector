<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline_service', 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\ref' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\service']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\DependencyInjection\\Definition', 'getDeprecationMessage', 'getDeprecation'), new MethodCallRename('Symfony\\Component\\DependencyInjection\\Alias', 'getDeprecationMessage', 'getDeprecation')]);
};
