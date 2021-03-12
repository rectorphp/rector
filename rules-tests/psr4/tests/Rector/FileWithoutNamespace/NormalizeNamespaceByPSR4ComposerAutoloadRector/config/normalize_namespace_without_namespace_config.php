<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source\DummyPSR4AutoloadWithoutNamespaceMatcher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, false);

    $services = $containerConfigurator->services();
    $services->set(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);
    $services->set(DummyPSR4AutoloadWithoutNamespaceMatcher::class);

    $services->alias(PSR4AutoloadNamespaceMatcherInterface::class, DummyPSR4AutoloadWithoutNamespaceMatcher::class);
};
