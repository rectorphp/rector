<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210510\Symplify\EasyCodingStandard\ValueObject\Option;
use RectorPrefix20210510\Symplify\EasyCodingStandard\ValueObject\Set\SetList;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(Option::SETS, [SetList::COMMON, SetList::PSR_12]);
};
