<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use RectorPrefix20210510\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210510\Symplify\ComposerJsonManipulator\ValueObject\Option;
use RectorPrefix20210510\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20210510\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210510\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix20210510\Symplify\SmartFileSystem\SmartFileSystem;
use function RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::INLINE_SECTIONS, ['keywords']);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20210510\Symplify\\ComposerJsonManipulator\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Bundle']);
    $services->set(SmartFileSystem::class);
    $services->set(PrivatesCaller::class);
    $services->set(ParameterProvider::class)->args([service(ContainerInterface::class)]);
    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)->factory([service(SymfonyStyleFactory::class), 'create']);
};
