<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector;

use Iterator;
use Rector\Core\Rector\Interface_\RemoveInterfacesRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;

final class RemoveInterfacesRectorTest extends AbstractRectorTestCase
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
            RemoveInterfacesRector::class => [
                '$interfacesToRemove' => [SomeInterface::class],
            ],
        ];
    }
}
