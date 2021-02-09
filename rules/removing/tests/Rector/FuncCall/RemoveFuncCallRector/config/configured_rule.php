<?php

use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveFuncCallRector::class)->call('configure', [[
        RemoveFuncCallRector::REMOVE_FUNC_CALLS => ValueObjectInliner::inline([

            new RemoveFuncCall('ini_get', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]), new RemoveFuncCall('ini_set', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]), ]
        ),
    ]]);
};
