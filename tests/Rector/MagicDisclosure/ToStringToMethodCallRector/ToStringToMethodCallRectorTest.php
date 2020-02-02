<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\ToStringToMethodCallRector;

use Iterator;
use Rector\Rector\MagicDisclosure\ToStringToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Config\ConfigCache;

final class ToStringToMethodCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
