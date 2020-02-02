<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\StaticCall\StaticCallToFunctionRector;

use Iterator;
use Rector\Rector\StaticCall\StaticCallToFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\StaticCall\StaticCallToFunctionRector\Source\SomeOldStaticClass;

final class StaticCallToFunctionRectorTest extends AbstractRectorTestCase
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
