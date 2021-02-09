<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector::class)->call(
        'configure',
        [[
            \Rector\RemovingStatic\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector::STATIC_CLASS_TYPES => [
                \Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods::class,
            ],
        ]]
    );
};
