<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
use Rector\Tests\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;
use Rector\Tests\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->configure([
            new ReturnArrayClassMethodToYield(EventSubscriberInterface::class, 'getSubscribedEvents'),
            new ReturnArrayClassMethodToYield(ParentTestCase::class, 'provide*'),
            new ReturnArrayClassMethodToYield(ParentTestCase::class, 'dataProvider*'),
            new ReturnArrayClassMethodToYield(TestCase::class, 'provideData'),
        ]);
};
