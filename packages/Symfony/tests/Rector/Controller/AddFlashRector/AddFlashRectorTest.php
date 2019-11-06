<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector;

use Iterator;
use Rector\Symfony\Rector\Controller\AddFlashRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddFlashRectorTest extends AbstractRectorTestCase
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
        return AddFlashRector::class;
    }
}
