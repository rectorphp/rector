<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# https://github.com/Seldaek/monolog/commit/39f8a20e6dadc0194e846b254c5f23d1c732290b#diff-dce565f403e044caa5e6a0d988339430
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addDebug', 'debug'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addInfo', 'info'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addNotice', 'notice'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addWarning', 'warning'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addError', 'error'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addCritical', 'critical'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addAlert', 'alert'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'addEmergency', 'emergency'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'warn', 'warning'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'err', 'error'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'crit', 'critical'), new \Rector\Renaming\ValueObject\MethodCallRename('Monolog\\Logger', 'emerg', 'emergency')]);
};
