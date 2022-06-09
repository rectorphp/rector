<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Set\Contract\SetListInterface;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use RectorPrefix20220609\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set('types_to_skip', [SymfonyRoutesProviderInterface::class, SetListInterface::class, RectorInterface::class]);
};
