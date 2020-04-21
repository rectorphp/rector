<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php74\Rector\Property\TypedPropertyRector;

final class UnionTypedPropertyRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureUnionTypes');
    }

    protected function getRectorClass(): string
    {
        return TypedPropertyRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::UNION_TYPES;
    }
}
