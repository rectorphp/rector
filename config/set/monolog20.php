<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/Seldaek/monolog/commit/39f8a20e6dadc0194e846b254c5f23d1c732290b#diff-dce565f403e044caa5e6a0d988339430
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Monolog\Logger' => [
                    'addDebug' => 'debug',
                    'addInfo' => 'info',
                    'addNotice' => 'notice',
                    'addWarning' => 'warning',
                    'addError' => 'error',
                    'addCritical' => 'critical',
                    'addAlert' => 'alert',
                    'addEmergency' => 'emergency',
                    'warn' => 'warning',
                    'err' => 'error',
                    'crit' => 'critical',
                    'emerg' => 'emergency',
                ],
            ],
        ]]);
};
