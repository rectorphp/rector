<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UnionTypeTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 8.0
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureUnionType');
    }

    protected function getRectorClass(): string
    {
        return RenameParamToMatchTypeRector::class;
    }

    protected function getPhpVersion(): int
    {
        return PhpVersionFeature::UNION_TYPES;
    }
}
