<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Iterator;
use Rector\Core\Util\StaticPhpVersion;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InheritanceTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureInheritance');
    }

    protected function getPhpVersion(): int
    {
        return StaticPhpVersion::getIntVersion('7.0');
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}
