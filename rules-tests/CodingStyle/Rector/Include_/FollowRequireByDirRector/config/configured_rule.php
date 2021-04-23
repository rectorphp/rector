<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FollowRequireByDirRector::class);
};
