<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'emerg', 'emergency'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'crit', 'critical'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'err', 'error'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'warn', 'warning'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'emerg', 'emergency'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'crit', 'critical'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'err', 'error'), new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'warn', 'warning')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\HttpKernel\\Debug\\ErrorHandler' => 'Symfony\\Component\\Debug\\ErrorHandler', 'Symfony\\Component\\HttpKernel\\Debug\\ExceptionHandler' => 'Symfony\\Component\\Debug\\ExceptionHandler', 'Symfony\\Component\\HttpKernel\\Exception\\FatalErrorException' => 'Symfony\\Component\\Debug\\Exception\\FatalErrorException', 'Symfony\\Component\\HttpKernel\\Exception\\FlattenException' => 'Symfony\\Component\\Debug\\Exception\\FlattenException', 'Symfony\\Component\\HttpKernel\\Log\\LoggerInterface' => 'Psr\\Log\\LoggerInterface', 'Symfony\\Component\\HttpKernel\\DependencyInjection\\RegisterListenersPass' => 'Symfony\\Component\\EventDispatcher\\DependencyInjection\\RegisterListenersPass', 'Symfony\\Component\\HttpKernel\\Log\\NullLogger' => 'Psr\\Log\\LoggerInterface']);
};
