<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector;

use Iterator;
use Rector\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

final class AddMethodParentCallRectorTest extends AbstractRectorTestCase
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
            AddMethodParentCallRector::class => [
                '$methodsByParentTypes' => [
                    ParentClassWithNewConstructor::class => ['__construct'],
                ],
            ],
        ];
    }
}
