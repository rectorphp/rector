<?php

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReturnArrayClassMethodToYieldRector::class)->call(
        'configure',
        [[
            ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => ValueObjectInliner::inline([

                new ReturnArrayClassMethodToYield(EventSubscriberInterface::class, 'getSubscribedEvents'),
                new ReturnArrayClassMethodToYield(ParentTestCase::class, 'provide*'),
                new ReturnArrayClassMethodToYield(ParentTestCase::class, 'dataProvider*'),
                new ReturnArrayClassMethodToYield(TestCase::class, 'provideData'),

            ]),
        ]]
    );
};
