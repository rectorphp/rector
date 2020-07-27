<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use Rector\PSR4\Tests\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source\DummyPSR4AutoloadNamespaceMatcher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();

    $services->set(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);

    $services->set(DummyPSR4AutoloadNamespaceMatcher::class);

    $services->alias(PSR4AutoloadNamespaceMatcherInterface::class, DummyPSR4AutoloadNamespaceMatcher::class);
};
