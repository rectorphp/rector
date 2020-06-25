<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector;

use Iterator;
use Rector\Core\Rector\MethodBody\FluentReplaceRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClassInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FluentReplaceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
            FluentReplaceRector::class => [
                '$classesToDefluent' => [FluentInterfaceClassInterface::class, '*Command'],
            ],
        ];
    }
}
