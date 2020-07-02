<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Printer\CommentPreserving;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CommentPreservingTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return TypedPropertyRector::class;
    }
}
