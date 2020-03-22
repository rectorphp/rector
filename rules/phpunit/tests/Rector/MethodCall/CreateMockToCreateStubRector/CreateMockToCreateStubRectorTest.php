<?php

declare(strict_types=1);

namespace Rector\phpunit\Tests\Rector\MethodCall\CreateMockToCreateStubRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\phpunit\Rector\MethodCall\CreateMockToCreateStubRector;

final class CreateMockToCreateStubRectorTest extends AbstractRectorTestCase
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
        return CreateMockToCreateStubRector::class;
    }
}
