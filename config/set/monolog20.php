<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# https://github.com/Seldaek/monolog/commit/39f8a20e6dadc0194e846b254c5f23d1c732290b#diff-dce565f403e044caa5e6a0d988339430
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addDebug', 'debug'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addInfo', 'info'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addNotice', 'notice'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addWarning', 'warning'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addError', 'error'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addCritical', 'critical'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addAlert', 'alert'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'addEmergency', 'emergency'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'warn', 'warning'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'err', 'error'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'crit', 'critical'), new MethodCallRename('RectorPrefix20220607\\Monolog\\Logger', 'emerg', 'emergency')]);
};
