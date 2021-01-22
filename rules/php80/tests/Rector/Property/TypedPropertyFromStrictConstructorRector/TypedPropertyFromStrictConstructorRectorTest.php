<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector;

use Rector\Php80\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @requires PHP 7.4
 */
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
        return TypedPropertyFromStrictConstructorRector::class;
    }
}
