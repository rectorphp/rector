<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Redirect301ToPermanentRedirectRectorTest extends AbstractRectorTestCase
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
        return Redirect301ToPermanentRedirectRector::class;
    }
}
