<?php

declare(strict_types=1);

use Rector\Transform\Tests\Rector\Class_\CommunityTestCaseRector\Fixture\SomeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeRector::class);
};
