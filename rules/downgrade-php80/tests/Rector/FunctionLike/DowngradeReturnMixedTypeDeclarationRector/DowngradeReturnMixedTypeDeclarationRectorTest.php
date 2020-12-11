<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector;

use Iterator;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeReturnMixedTypeDeclarationRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return DowngradeReturnMixedTypeDeclarationRector::class;
    }
}
