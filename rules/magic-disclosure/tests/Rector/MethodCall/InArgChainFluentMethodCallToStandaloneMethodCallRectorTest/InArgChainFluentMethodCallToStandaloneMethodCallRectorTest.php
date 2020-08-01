<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InArgChainFluentMethodCallToStandaloneMethodCallRectorTest extends AbstractRectorTestCase
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
        return InArgFluentChainMethodCallToStandaloneMethodCallRector::class;
    }
}
