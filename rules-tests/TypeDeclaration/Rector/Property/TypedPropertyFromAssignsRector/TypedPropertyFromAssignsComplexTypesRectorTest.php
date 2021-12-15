<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TypedPropertyFromAssignsComplexTypesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureComplexTypes');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/complex_types.php';
    }
}
