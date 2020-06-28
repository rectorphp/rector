<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MagicDisclosure\Rector\MethodCall\DefluentMethodCallRector;
use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClassInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DefluentMethodCallRectorTest extends AbstractRectorTestCase
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
            DefluentMethodCallRector::class => [
                '$namesToDefluent' => [FluentInterfaceClassInterface::class, '*Command'],
            ],
        ];
    }
}
