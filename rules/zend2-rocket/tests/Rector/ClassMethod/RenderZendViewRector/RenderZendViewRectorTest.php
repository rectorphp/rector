<?php

declare(strict_types=1);

namespace Rector\Zend2Rocket\Tests\Rector\ClassMethod\RenderZendViewRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class RenderZendViewRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\Zend2Rocket\Rector\ClassMethod\RenderZendViewRector::class;
    }
}
