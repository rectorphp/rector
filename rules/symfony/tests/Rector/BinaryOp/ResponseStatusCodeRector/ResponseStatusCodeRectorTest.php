<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ResponseStatusCodeRectorTest extends AbstractRectorTestCase
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
        return ResponseStatusCodeRector::class;
    }
}
