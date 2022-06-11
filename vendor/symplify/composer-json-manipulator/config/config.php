<?php

declare (strict_types=1);
namespace RectorPrefix202206;

use RectorPrefix202206\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202206\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix202206\Symplify\ComposerJsonManipulator\ValueObject\Option;
use RectorPrefix202206\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix202206\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202206\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix202206\Symplify\SmartFileSystem\SmartFileSystem;
use function RectorPrefix202206\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::INLINE_SECTIONS, ['keywords']);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('RectorPrefix202206\Symplify\\ComposerJsonManipulator\\', __DIR__ . '/../src');
    $services->set(SmartFileSystem::class);
    $services->set(PrivatesCaller::class);
    $services->set(ParameterProvider::class)->args([service('service_container')]);
    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)->factory([service(SymfonyStyleFactory::class), 'create']);
};
