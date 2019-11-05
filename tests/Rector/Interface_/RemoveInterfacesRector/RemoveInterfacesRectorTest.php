<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Interface_\RemoveInterfacesRector;

use Iterator;
use Rector\Rector\Interface_\RemoveInterfacesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

final class RemoveInterfacesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveInterfacesRector::class => [
                '$interfacesToRemove' => [SomeInterface::class],
            ],
        ];
    }
}
