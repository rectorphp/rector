<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MagicDisclosure\ToStringToMethodCallRector;

use Iterator;
use Rector\Core\Rector\MagicDisclosure\ToStringToMethodCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Config\ConfigCache;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ToStringToMethodCallRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ToStringToMethodCallRector::class => [
                '$methodNamesByType' => [
                    ConfigCache::class => 'getPath',
                ],
            ],
        ];
    }
}
