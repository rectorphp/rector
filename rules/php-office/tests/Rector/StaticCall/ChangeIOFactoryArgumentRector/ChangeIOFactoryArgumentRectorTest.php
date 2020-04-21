<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Tests\Rector\StaticCall\ChangeIOFactoryArgumentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector;

final class ChangeIOFactoryArgumentRectorTest extends AbstractRectorTestCase
{
    /**
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
        return ChangeIOFactoryArgumentRector::class;
    }
}
