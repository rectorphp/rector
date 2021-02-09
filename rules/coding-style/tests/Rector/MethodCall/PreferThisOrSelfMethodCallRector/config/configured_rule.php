<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector::class)->call('configure', [[
        \Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
            \Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase::class => 'self',
            \Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass::class => 'this',
            \PHPUnit\Framework\TestCase::class => 'self',
        ],
    ]]);
};
