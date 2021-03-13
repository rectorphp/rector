<?php

declare(strict_types=1);

namespace Rector\Tests\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;

use Iterator;
use Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MakeTypedPropertyNullableIfCheckedRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MakeTypedPropertyNullableIfCheckedRector::class;
    }
}
