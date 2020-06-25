<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;

use Iterator;
use Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;
use Rector\CodingStyle\Tests\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Source\EventSubscriberInterface;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class YieldClassMethodToArrayClassMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
            YieldClassMethodToArrayClassMethodRector::class => [
                '$methodsByType' => [
                    EventSubscriberInterface::class => ['getSubscribedEvents'],
                ],
            ],
        ];
    }
}
