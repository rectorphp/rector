<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\Class_\TypedPropertyFromStrictConstructorRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TypedPropertyFromStrictConstructorRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\Php80\Rector\Class_\TypedPropertyFromStrictConstructorRector::class;
    }
}
