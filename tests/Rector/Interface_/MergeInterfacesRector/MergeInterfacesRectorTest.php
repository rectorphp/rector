<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Interface_\MergeInterfacesRector;

use Iterator;
use Rector\Rector\Interface_\MergeInterfacesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

final class MergeInterfacesRectorTest extends AbstractRectorTestCase
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
            MergeInterfacesRector::class => [
                '$oldToNewInterfaces' => [
                    SomeOldInterface::class => SomeInterface::class,
                ],
            ],
        ];
    }
}
