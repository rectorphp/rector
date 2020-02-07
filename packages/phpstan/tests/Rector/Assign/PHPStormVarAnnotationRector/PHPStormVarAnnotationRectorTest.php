<?php

declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Assign\PHPStormVarAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector;

final class PHPStormVarAnnotationRectorTest extends AbstractRectorTestCase
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
        return PHPStormVarAnnotationRector::class;
    }
}
