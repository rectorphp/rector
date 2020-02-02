<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedParamTypeRector;
use Rector\ValueObject\PhpVersionFeature;

final class AddMethodCallBasedParamTypeRectorTest extends AbstractRectorTestCase
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
        return AddMethodCallBasedParamTypeRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_UNION_TYPES;
    }
}
