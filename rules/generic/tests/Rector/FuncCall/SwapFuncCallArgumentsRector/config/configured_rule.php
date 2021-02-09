<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector::class)->call('configure', [[
        \Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector::FUNCTION_ARGUMENT_SWAPS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Generic\ValueObject\SwapFuncCallArguments('some_function', [1, 0]),
        ]
        ),
    ]]);
};
