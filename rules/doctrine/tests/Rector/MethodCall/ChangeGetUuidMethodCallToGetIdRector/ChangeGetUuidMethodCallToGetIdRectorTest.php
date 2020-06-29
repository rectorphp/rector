<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeGetUuidMethodCallToGetIdRectorTest extends AbstractRectorTestCase
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
        return ChangeGetUuidMethodCallToGetIdRector::class;
    }
}
