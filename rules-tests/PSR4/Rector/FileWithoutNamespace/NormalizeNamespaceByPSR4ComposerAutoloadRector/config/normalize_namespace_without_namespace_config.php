<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source\DummyPSR4AutoloadWithoutNamespaceMatcher;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $rectorConfig->disableImportNames();
    $rectorConfig->rule(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);

    $services->set(DummyPSR4AutoloadWithoutNamespaceMatcher::class);

    $services->alias(PSR4AutoloadNamespaceMatcherInterface::class, DummyPSR4AutoloadWithoutNamespaceMatcher::class);
};
