<?php

declare(strict_types=1);

namespace Rector\JMS\Tests\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\JMS\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector;

final class RemoveJmsInjectParamsAnnotationRectorTest extends AbstractRectorTestCase
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
        return RemoveJmsInjectParamsAnnotationRector::class;
    }
}
