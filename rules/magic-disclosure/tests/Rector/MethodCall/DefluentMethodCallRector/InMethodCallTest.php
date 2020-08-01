<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\MethodCall\DefluentMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InMethodCallTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureInMethodCall');
    }

    protected function getRectorClass(): string
    {
        return DefluentMethodCallRector::class;
    }
}
