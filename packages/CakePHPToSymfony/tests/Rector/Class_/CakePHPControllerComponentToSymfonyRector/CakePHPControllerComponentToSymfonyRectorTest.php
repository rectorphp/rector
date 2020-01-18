<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerComponentToSymfonyRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerComponentToSymfonyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPControllerComponentToSymfonyRectorTest extends AbstractRectorTestCase
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
        return CakePHPControllerComponentToSymfonyRector::class;
    }
}
