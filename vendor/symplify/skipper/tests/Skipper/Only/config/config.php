<?php

declare (strict_types=1);
namespace RectorPrefix20210511;

use RectorPrefix20210511\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\IncludeThisClass;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletely;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletelyToo;
use RectorPrefix20210511\Symplify\Skipper\ValueObject\Option;
return static function (\RectorPrefix20210511\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\RectorPrefix20210511\Symplify\Skipper\ValueObject\Option::ONLY, [
        \RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\IncludeThisClass::class => ['SomeFileToOnlyInclude.php'],
        // these 2 lines should be identical
        \RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletely::class => null,
        \RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletelyToo::class,
    ]);
};
