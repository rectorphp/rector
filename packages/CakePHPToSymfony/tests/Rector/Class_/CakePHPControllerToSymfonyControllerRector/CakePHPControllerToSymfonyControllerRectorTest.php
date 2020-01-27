<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPControllerToSymfonyControllerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPControllerToSymfonyControllerRectorTest extends AbstractRectorTestCase
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
        return CakePHPControllerToSymfonyControllerRector::class;
    }
}
