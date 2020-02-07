<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodBody\ReturnThisRemoveRector;

use Iterator;
use Rector\Core\Rector\MethodBody\ReturnThisRemoveRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnThisRemoveRectorTest extends AbstractRectorTestCase
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
            ReturnThisRemoveRector::class => [
                '$classesToDefluent' => [
                    'Rector\Core\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture\SomeClass',
                    'Rector\Core\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture\SomeClassWithReturnAnnotations',
                ],
            ],
        ];
    }
}
