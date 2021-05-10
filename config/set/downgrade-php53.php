<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_53);
    $services = $containerConfigurator->services();
    $services->set(DirConstToFileConstRector::class);
};
