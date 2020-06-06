<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sensio\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;

final class ReplaceSensioRouteAnnotationWithSymfonyRectorTest extends AbstractRectorTestCase
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
        return ReplaceSensioRouteAnnotationWithSymfonyRector::class;
    }
}
