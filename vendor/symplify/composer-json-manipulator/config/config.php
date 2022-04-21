<?php

declare (strict_types=1);
namespace RectorPrefix20220421;

use RectorPrefix20220421\Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220421\Symplify\ComposerJsonManipulator\ValueObject\Option;
use RectorPrefix20220421\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20220421\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220421\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix20220421\Symplify\SmartFileSystem\SmartFileSystem;
use function RectorPrefix20220421\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20220421\Symplify\ComposerJsonManipulator\ValueObject\Option::INLINE_SECTIONS, ['keywords']);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20220421\Symplify\\ComposerJsonManipulator\\', __DIR__ . '/../src');
    $services->set(\RectorPrefix20220421\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\RectorPrefix20220421\Symplify\PackageBuilder\Reflection\PrivatesCaller::class);
    $services->set(\RectorPrefix20220421\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->args([\RectorPrefix20220421\Symfony\Component\DependencyInjection\Loader\Configurator\service('service_container')]);
    $services->set(\RectorPrefix20220421\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\RectorPrefix20220421\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\RectorPrefix20220421\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20220421\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
};
