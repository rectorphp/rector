<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\RectorGenerator\ValueObject\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    // needed for interactive command
    $parameters->set(\Rector\RectorGenerator\ValueObject\Option::RULES_DIRECTORY, null);
    $parameters->set(\Rector\RectorGenerator\ValueObject\Option::SET_LIST_CLASSES, []);
};
