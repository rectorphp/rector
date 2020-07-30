<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UnsetAndIssetToMethodCallRectorTest extends AbstractRectorTestCase
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
            UnsetAndIssetToMethodCallRector::class => [
                UnsetAndIssetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                    LocalContainer::class => [
                        'isset' => 'hasService',
                        'unset' => 'removeService',
                    ],
                ],
            ],
        ];
    }
}
