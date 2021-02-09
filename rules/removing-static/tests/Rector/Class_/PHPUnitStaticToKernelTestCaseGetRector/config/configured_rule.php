<?php

use Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector;
use Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PHPUnitStaticToKernelTestCaseGetRector::class)->call(
        'configure',
        [[
            PHPUnitStaticToKernelTestCaseGetRector::STATIC_CLASS_TYPES => [ClassWithStaticMethods::class],
        ]]
    );
};
