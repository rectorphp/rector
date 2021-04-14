<?php

declare(strict_types=1);

use Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteTesterToPHPUnit\Rector\Class_\RenameTesterTestToPHPUnitToTestFileRector;
use Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NetteTesterClassToPHPUnitClassRector::class);
    $services->set(NetteAssertToPHPUnitAssertRector::class);
    $services->set(RenameTesterTestToPHPUnitToTestFileRector::class);
};
