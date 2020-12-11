<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP < 8.0
 */
final class CompleteVarDocTypePropertyRectorTest extends AbstractRectorTestCase
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
        return CompleteVarDocTypePropertyRector::class;
    }
}
