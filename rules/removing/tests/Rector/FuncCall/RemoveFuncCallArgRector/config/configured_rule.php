<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::class)->call('configure', [[
        \Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Removing\ValueObject\RemoveFuncCallArg('ldap_first_attribute', 2),
























            
        ]),
    ]]);
};
