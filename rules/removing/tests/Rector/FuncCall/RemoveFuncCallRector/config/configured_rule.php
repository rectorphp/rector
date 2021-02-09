<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Removing\Rector\FuncCall\RemoveFuncCallRector::class)->call('configure', [[
        \Rector\Removing\Rector\FuncCall\RemoveFuncCallRector::REMOVE_FUNC_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Removing\ValueObject\RemoveFuncCall('ini_get', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]), new \Rector\Removing\ValueObject\RemoveFuncCall('ini_set', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]), ]
        ),
    ]]);
};
