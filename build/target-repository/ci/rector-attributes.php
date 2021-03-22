<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// route class must exist and be loaded, as annotation parser uses dynamic reflection
require_once __DIR__ . '/../stubs/Symfony/Component/Routing/Annotation/Route.php';

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SymfonySetList::SYMFONY_52);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);
};
