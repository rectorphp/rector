<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;

use Iterator;
use Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @preserveGlobalState disabled
 */
final class Php8Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     * @requires PHP 8
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp8');
    }

    protected function getRectorClass(): string
    {
        return PrivatizeLocalOnlyMethodRector::class;
    }
}
