<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyTypeDeclarationRectorTest extends AbstractRectorTestCase
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
        return PropertyTypeDeclarationRector::class;
    }
}
