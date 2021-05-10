<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Only\Source\IncludeThisClass;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletely;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletelyToo;
use RectorPrefix20210510\Symplify\Skipper\ValueObject\Option;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::ONLY, [
        IncludeThisClass::class => ['SomeFileToOnlyInclude.php'],
        // these 2 lines should be identical
        SkipCompletely::class => null,
        SkipCompletelyToo::class,
    ]);
};
