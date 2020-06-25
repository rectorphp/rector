<?php

declare(strict_types=1);

namespace Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Twig\Rector\SimpleFunctionAndFilterRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimpleFunctionAndFilterRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SimpleFunctionAndFilterRector::class;
    }
}
