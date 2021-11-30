<?php

declare(strict_types=1);

use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(StringToClassConstantRector::class)
        ->configure([

            new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT'),
            new StringToClassConstant('compiler.to_class', 'Yet\AnotherClass', 'class'),

        ]);
};
