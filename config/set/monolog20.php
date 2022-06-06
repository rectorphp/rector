<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
# https://github.com/Seldaek/monolog/commit/39f8a20e6dadc0194e846b254c5f23d1c732290b#diff-dce565f403e044caa5e6a0d988339430
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Monolog\\Logger', 'addDebug', 'debug'), new MethodCallRename('Monolog\\Logger', 'addInfo', 'info'), new MethodCallRename('Monolog\\Logger', 'addNotice', 'notice'), new MethodCallRename('Monolog\\Logger', 'addWarning', 'warning'), new MethodCallRename('Monolog\\Logger', 'addError', 'error'), new MethodCallRename('Monolog\\Logger', 'addCritical', 'critical'), new MethodCallRename('Monolog\\Logger', 'addAlert', 'alert'), new MethodCallRename('Monolog\\Logger', 'addEmergency', 'emergency'), new MethodCallRename('Monolog\\Logger', 'warn', 'warning'), new MethodCallRename('Monolog\\Logger', 'err', 'error'), new MethodCallRename('Monolog\\Logger', 'crit', 'critical'), new MethodCallRename('Monolog\\Logger', 'emerg', 'emergency')]);
};
