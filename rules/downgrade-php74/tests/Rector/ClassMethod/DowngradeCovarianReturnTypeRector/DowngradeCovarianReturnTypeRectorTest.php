<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovarianReturnTypeRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovarianReturnTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeCovarianReturnTypeRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
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
        return DowngradeCovarianReturnTypeRector::class;
    }

    protected function getPhpVersion(): int
    {
        return PhpVersionFeature::COVARIANT_RETURN - 1;
    }
}
