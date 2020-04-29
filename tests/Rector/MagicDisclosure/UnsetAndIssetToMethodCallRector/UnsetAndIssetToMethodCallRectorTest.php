<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;

use Iterator;
use Rector\Core\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector\Source\LocalContainer;

final class UnsetAndIssetToMethodCallRectorTest extends AbstractRectorTestCase
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
            UnsetAndIssetToMethodCallRector::class => [
                '$typeToMethodCalls' => [
                    LocalContainer::class => [
                        'isset' => 'hasService',
                        'unset' => 'removeService',
                    ],
                ],
            ],
        ];
    }
}
