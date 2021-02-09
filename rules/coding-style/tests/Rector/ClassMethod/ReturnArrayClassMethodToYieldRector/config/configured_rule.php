<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector::class)->call(
        'configure',
        [[
            \Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield(
                    \Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface::class,
                    'getSubscribedEvents'
                ),
                new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield(
                    \Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase::class,
                    'provide*'
                ),
                new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield(
                    \Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase::class,
                    'dataProvider*'
                ),
                new \Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield(
                    \PHPUnit\Framework\TestCase::class,
                    'provideData'
                ),

                
            ]),
        ]]
    );
};
