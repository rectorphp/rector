<?php

declare (strict_types=1);
namespace RectorPrefix20210514;

use RectorPrefix20210514\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210514\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use RectorPrefix20210514\Symplify\Skipper\ValueObject\Option;
use RectorPrefix20210514\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
return static function (\RectorPrefix20210514\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20210514\Symplify\Skipper\ValueObject\Option::SKIP, []);
    $parameters->set(\RectorPrefix20210514\Symplify\Skipper\ValueObject\Option::ONLY, []);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('RectorPrefix20210514\Symplify\\Skipper\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Bundle', __DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/ValueObject']);
    $services->set(\RectorPrefix20210514\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker::class);
    $services->set(\RectorPrefix20210514\Symplify\SmartFileSystem\Normalizer\PathNormalizer::class);
};
