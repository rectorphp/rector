<?php

declare(strict_types=1);

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;

use Iterator;
use Rector\Symfony5\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class LogoutSuccessHandlerToLogoutEventSubscriberRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return LogoutSuccessHandlerToLogoutEventSubscriberRector::class;
    }
}
