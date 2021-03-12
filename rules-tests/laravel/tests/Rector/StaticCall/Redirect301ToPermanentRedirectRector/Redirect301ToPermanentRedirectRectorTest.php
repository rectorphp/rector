<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector;

use Iterator;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Redirect301ToPermanentRedirectRectorTest extends AbstractRectorTestCase
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
        return Redirect301ToPermanentRedirectRector::class;
    }
}
