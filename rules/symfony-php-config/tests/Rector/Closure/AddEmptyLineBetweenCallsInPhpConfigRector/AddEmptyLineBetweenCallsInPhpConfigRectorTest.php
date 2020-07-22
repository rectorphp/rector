<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Rector\Closure\AddEmptyLineBetweenCallsInPhpConfigRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\SymfonyPhpConfig\Rector\Closure\AddEmptyLineBetweenCallsInPhpConfigRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddEmptyLineBetweenCallsInPhpConfigRectorTest extends AbstractRectorTestCase
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
        return AddEmptyLineBetweenCallsInPhpConfigRector::class;
    }
}
