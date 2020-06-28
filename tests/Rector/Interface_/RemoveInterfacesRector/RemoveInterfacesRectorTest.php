<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector;

use Iterator;
use Rector\Core\Rector\Interface_\RemoveInterfacesRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\Source\SomeInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveInterfacesRectorTest extends AbstractRectorTestCase
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
            RemoveInterfacesRector::class => [
                '$interfacesToRemove' => [SomeInterface::class],
            ],
        ];
    }
}
