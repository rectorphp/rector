<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector;

use Iterator;
use Rector\Core\Rector\MagicDisclosure\GetAndSetToMethodCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\Klarka;
use Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

final class GetAndSetToMethodCallRectorTest extends AbstractRectorTestCase
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
            GetAndSetToMethodCallRector::class => [
                '$typeToMethodCalls' => [
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
