<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use RectorPrefix20220609\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220609\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use RectorPrefix20220609\Symplify\Skipper\ValueObject\Option;
use RectorPrefix20220609\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SKIP, []);
    $parameters->set(Option::ONLY, []);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('RectorPrefix20220609\Symplify\\Skipper\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/ValueObject']);
    $services->set(ClassLikeExistenceChecker::class);
    $services->set(PathNormalizer::class);
};
