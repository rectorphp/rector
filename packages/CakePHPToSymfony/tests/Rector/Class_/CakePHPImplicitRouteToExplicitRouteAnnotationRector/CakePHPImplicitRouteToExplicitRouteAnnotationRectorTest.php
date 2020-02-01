<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPImplicitRouteToExplicitRouteAnnotationRectorTest extends AbstractRectorTestCase
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
        return CakePHPImplicitRouteToExplicitRouteAnnotationRector::class;
    }
}
