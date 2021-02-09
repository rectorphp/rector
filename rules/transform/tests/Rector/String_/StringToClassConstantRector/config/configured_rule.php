<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\String_\StringToClassConstantRector::class)->call('configure', [[
        \Rector\Transform\Rector\String_\StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\StringToClassConstant(
                'compiler.post_dump',
                'Yet\AnotherClass',
                'CONSTANT'
            ),
            new \Rector\Transform\ValueObject\StringToClassConstant('compiler.to_class', 'Yet\AnotherClass', 'class'),
























            
        ]),
    ]]);
};
