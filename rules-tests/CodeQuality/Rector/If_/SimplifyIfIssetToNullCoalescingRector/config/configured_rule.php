<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SimplifyIfIssetToNullCoalescingRector::class);
};
