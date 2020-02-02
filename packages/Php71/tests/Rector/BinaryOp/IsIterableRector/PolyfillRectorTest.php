<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\BinaryOp\IsIterableRector;

use Iterator;
use Rector\Php71\Rector\BinaryOp\IsIterableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ValueObject\PhpVersionFeature;

final class PolyfillRectorTest extends AbstractRectorTestCase
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
        return IsIterableRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::ITERABLE_TYPE;
    }
}
