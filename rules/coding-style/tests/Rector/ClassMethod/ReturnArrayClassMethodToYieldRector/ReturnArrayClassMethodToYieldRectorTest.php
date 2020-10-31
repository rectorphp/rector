<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReturnArrayClassMethodToYieldRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReturnArrayClassMethodToYieldRector::class => [
                ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => [
                    new ReturnArrayClassMethodToYield(EventSubscriberInterface::class, 'getSubscribedEvents'),
                    new ReturnArrayClassMethodToYield(ParentTestCase::class, 'provide*'),
                    new ReturnArrayClassMethodToYield(ParentTestCase::class, 'dataProvider*'),
                    new ReturnArrayClassMethodToYield(TestCase::class, 'provideData'),
                ],
            ],
        ];
    }
}
