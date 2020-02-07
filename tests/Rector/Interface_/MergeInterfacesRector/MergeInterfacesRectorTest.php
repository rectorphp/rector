<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector;

use Iterator;
use Rector\Core\Rector\Interface_\MergeInterfacesRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\Source\SomeOldInterface;

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
