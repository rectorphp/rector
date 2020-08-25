<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;
use Rector\CodingStyle\ValueObject\MethodToYield;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReturnArrayClassMethodToYieldRector::class => [
                ReturnArrayClassMethodToYieldRector::METHODS_TO_YIELDS => [
                    new MethodToYield(EventSubscriberInterface::class, 'getSubscribedEvents'),
                    new MethodToYield(ParentTestCase::class, 'provide*'),
                    new MethodToYield(ParentTestCase::class, 'dataProvider*'),
                    new MethodToYield(TestCase::class, 'provideData'),
                ],
            ],
        ];
    }
}
