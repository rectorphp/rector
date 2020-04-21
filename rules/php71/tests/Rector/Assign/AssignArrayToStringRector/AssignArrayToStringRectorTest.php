<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;

final class AssignArrayToStringRectorTest extends AbstractRectorTestCase
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
        return AssignArrayToStringRector::class;
    }
}
