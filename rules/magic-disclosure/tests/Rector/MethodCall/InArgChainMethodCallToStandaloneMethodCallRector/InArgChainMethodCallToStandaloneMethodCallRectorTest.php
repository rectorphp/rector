<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InArgChainMethodCallToStandaloneMethodCallRectorTest extends AbstractRectorTestCase
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
        return InArgChainMethodCallToStandaloneMethodCallRector::class;
    }
}
