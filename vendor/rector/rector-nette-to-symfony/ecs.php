<?php

declare (strict_types=1);
namespace RectorPrefix20210615;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210615\Symplify\EasyCodingStandard\ValueObject\Option;
use RectorPrefix20210615\Symplify\EasyCodingStandard\ValueObject\Set\SetList;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\RectorPrefix20210615\Symplify\EasyCodingStandard\ValueObject\Set\SetList::COMMON);
    $containerConfigurator->import(\RectorPrefix20210615\Symplify\EasyCodingStandard\ValueObject\Set\SetList::PSR_12);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20210615\Symplify\EasyCodingStandard\ValueObject\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
};
