<?php

use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(StringToClassConstantRector::class)->call('configure', [[
        StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => ValueObjectInliner::inline([

            new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT'),
            new StringToClassConstant('compiler.to_class', 'Yet\AnotherClass', 'class'),

        ]),
    ]]);
};
