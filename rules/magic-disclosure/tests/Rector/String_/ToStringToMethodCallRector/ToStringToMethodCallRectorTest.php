<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\String_\ToStringToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\String_\ToStringToMethodCallRector;
use Symfony\Component\Config\ConfigCache;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ToStringToMethodCallRectorTest extends AbstractRectorTestCase
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
            ToStringToMethodCallRector::class => [
                ToStringToMethodCallRector::METHOD_NAMES_BY_TYPE => [
                    ConfigCache::class => 'getPath',
                ],
            ],
        ];
    }
}
