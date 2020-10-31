<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\Assign\GetAndSetToMethodCallRector;

use Iterator;
use Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector;
use Rector\MagicDisclosure\Tests\Rector\Assign\GetAndSetToMethodCallRector\Source\Klarka;
use Rector\MagicDisclosure\Tests\Rector\Assign\GetAndSetToMethodCallRector\Source\SomeContainer;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetAndSetToMethodCallRectorTest extends AbstractRectorTestCase
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            GetAndSetToMethodCallRector::class => [
                GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                    SomeContainer::class => [
                        'get' => 'getService',
                        'set' => 'addService',
                    ],
                    Klarka::class => [
                        'get' => 'get',
                    ],
                ],
            ],
        ];
    }
}
