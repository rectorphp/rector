<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\StaticCall\StaticCallToFunctionRector;

use Iterator;
use Rector\Core\Rector\StaticCall\StaticCallToFunctionRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StaticCallToFunctionRectorTest extends AbstractRectorTestCase
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
            StaticCallToFunctionRector::class => [
                '$staticCallToFunctionByType' => [
                    SomeOldStaticClass::class => [
                        'render' => 'view',
                    ],
                ],
            ],
        ];
    }
}
