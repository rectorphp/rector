<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\RedirectToRouteRector;

use Iterator;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RedirectToRouteRectorTest extends AbstractRectorTestCase
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
        return RedirectToRouteRector::class;
    }
}
