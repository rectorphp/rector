<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;

use Iterator;
use Nette\DI\Container;
use Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

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
                    Container::class => [
                        'isset' => 'hasService',
                        'unset' => 'removeService',
                    ],
                ],
            ],
        ];
    }
}
