<?php

declare(strict_types=1);

use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveFuncCallRector::class)
        ->configure([
            new RemoveFuncCall('ini_get', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]),
            new RemoveFuncCall('ini_set', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]),
        ]);
};
