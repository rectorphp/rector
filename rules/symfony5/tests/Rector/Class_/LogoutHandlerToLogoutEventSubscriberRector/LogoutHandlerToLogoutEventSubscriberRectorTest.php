<?php

declare(strict_types=1);

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;

use Rector\Symfony5\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class LogoutHandlerToLogoutEventSubscriberRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return LogoutHandlerToLogoutEventSubscriberRector::class;
    }
}
