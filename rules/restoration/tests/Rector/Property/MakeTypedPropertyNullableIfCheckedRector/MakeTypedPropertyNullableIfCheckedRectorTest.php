<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;

final class MakeTypedPropertyNullableIfCheckedRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MakeTypedPropertyNullableIfCheckedRector::class;
    }
}
