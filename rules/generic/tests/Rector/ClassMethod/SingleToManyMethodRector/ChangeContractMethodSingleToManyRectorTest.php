<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector;

use Iterator;
use Rector\Generic\Rector\ClassMethod\SingleToManyMethodRector;
use Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;
use Rector\Generic\ValueObject\SingleToManyMethod;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SingleToManyMethodRectorTest extends AbstractRectorTestCase
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
            SingleToManyMethodRector::class => [
                SingleToManyMethodRector::SINGLES_TO_MANY_METHODS => [
                    new SingleToManyMethod(OneToManyInterface::class, 'getNode', 'getNodes'),
                ],
            ],
        ];
    }
}
