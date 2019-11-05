<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

use Iterator;
use Rector\Rector\MethodBody\ReturnThisRemoveRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnThisRemoveRectorTest extends AbstractRectorTestCase
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
            ReturnThisRemoveRector::class => [
                '$classesToDefluent' => [
                    'Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture\SomeClass',
                    'Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture\SomeClassWithReturnAnnotations',
                ],
            ],
        ];
    }
}
