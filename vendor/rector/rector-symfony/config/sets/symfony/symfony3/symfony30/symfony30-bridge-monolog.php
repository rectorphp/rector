<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Bridge\\Monolog\\Logger' => 'Psr\\Log\\LoggerInterface']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'emerg', 'emergency'), new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'crit', 'critical'), new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'err', 'error'), new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'warn', 'warning')]);
};
