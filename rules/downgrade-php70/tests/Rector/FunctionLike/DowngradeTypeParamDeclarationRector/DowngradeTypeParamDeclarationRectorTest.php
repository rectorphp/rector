<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeParamDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeTypeParamDeclarationRectorTest extends AbstractRectorTestCase
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
        return DowngradeTypeParamDeclarationRector::class;
    }

    protected function getPhpVersion(): int
    {
        return PhpVersionFeature::NULLABLE_TYPE - 1;
    }
}
