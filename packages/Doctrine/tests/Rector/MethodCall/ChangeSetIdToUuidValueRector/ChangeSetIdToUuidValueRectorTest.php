<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector;

use Iterator;
use Rector\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSetIdToUuidValueRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ChangeSetIdToUuidValueRector::class;
    }
}
