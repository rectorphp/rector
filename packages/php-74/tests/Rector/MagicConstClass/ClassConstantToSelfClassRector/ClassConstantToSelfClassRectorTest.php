<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\MagicConstClass\ClassConstantToSelfClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\MagicConstClass\ClassConstantToSelfClassRector;

final class ClassConstantToSelfClassRectorTest extends AbstractRectorTestCase
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
        return ClassConstantToSelfClassRector::class;
    }
}
