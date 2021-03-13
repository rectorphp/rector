<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OpenSourceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureOpenSource');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/fixture_open_source.php';
    }
}
