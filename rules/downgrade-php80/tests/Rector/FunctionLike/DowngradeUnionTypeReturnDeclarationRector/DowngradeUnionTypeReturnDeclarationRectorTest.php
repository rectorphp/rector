<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Tests\Rector\Property\DowngradeUnionTypeReturnDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeReturnDeclarationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP >= 8.0
 */
final class DowngradeUnionTypeReturnDeclarationRectorTest extends AbstractRectorTestCase
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
        return DowngradeUnionTypeReturnDeclarationRector::class;
    }
}
