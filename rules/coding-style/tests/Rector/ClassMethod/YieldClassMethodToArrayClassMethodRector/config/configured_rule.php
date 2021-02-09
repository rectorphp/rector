<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector::class)->call(
        'configure',
        [[
            \Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector::METHODS_BY_TYPE => [
                \Rector\CodingStyle\Tests\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Source\EventSubscriberInterface::class => [
                    'getSubscribedEvents',
                ],
            ],
        ]]
    );
};
