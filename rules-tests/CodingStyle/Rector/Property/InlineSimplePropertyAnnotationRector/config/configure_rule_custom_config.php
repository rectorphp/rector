<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector;

use Rector\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $rectorServiceConfigurator = $services->set(InlineSimplePropertyAnnotationRector::class);
    /** @phpstan-ignore-next-line  */
    $rectorServiceConfigurator->configure(['custom-var']);
};
