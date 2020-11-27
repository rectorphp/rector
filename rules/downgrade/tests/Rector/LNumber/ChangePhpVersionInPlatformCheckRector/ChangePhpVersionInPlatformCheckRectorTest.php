<?php

declare(strict_types=1);

namespace Rector\Downgrade\Tests\Rector\LNumber\ChangePhpVersionInPlatformCheckRector;

use Iterator;
use Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangePhpVersionInPlatformCheckRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ChangePhpVersionInPlatformCheckRector::class => [
                ChangePhpVersionInPlatformCheckRector::TARGET_PHP_VERSION => 70100,
            ],
        ];
    }
}
