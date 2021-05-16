<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\Core\Tests\Issues\Rector\MethodCall\InfinityLoopRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(InfinityLoopRector::class);
    $services->set(SimplifyDeMorganBinaryRector::class);
};
