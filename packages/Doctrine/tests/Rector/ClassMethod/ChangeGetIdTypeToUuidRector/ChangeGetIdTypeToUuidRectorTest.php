<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\ClassMethod\ChangeGetIdTypeToUuidRector;

use Iterator;
use Rector\Doctrine\Rector\ClassMethod\ChangeGetIdTypeToUuidRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeGetIdTypeToUuidRectorTest extends AbstractRectorTestCase
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
        return ChangeGetIdTypeToUuidRector::class;
    }
}
