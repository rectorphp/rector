<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Set\Contract\SetListInterface;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set('types_to_skip', [\Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface::class, \Rector\Set\Contract\SetListInterface::class, \Rector\Core\Contract\Rector\RectorInterface::class]);
};
