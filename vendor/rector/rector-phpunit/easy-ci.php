<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PHPUnit\Naming\TestClassNameResolverInterface;
use Rector\Set\Contract\SetListInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220501\Symplify\EasyCI\ValueObject\Option;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20220501\Symplify\EasyCI\ValueObject\Option::TYPES_TO_SKIP, [\Rector\PHPUnit\Naming\TestClassNameResolverInterface::class, \Rector\Core\Contract\Rector\RectorInterface::class, \Rector\Set\Contract\SetListInterface::class]);
};
