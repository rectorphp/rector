<?php

declare(strict_types=1);

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class LogoutSuccessHandlerToLogoutEventSubscriberRectorTest extends AbstractRectorTestCase
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
        return \Rector\Symfony5\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector::class;
    }
}
