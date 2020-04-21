<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;

final class PrivatizeLocalOnlyMethodRectorTest extends AbstractRectorTestCase
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
        return PrivatizeLocalOnlyMethodRector::class;
    }
}
