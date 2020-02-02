<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector;

use Iterator;
use Rector\Rector\Class_\ParentClassToTraitsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;

final class ParentClassToTraitsRectorTest extends AbstractRectorTestCase
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
            ParentClassToTraitsRector::class => [
                '$parentClassToTraits' => [
                    ParentObject::class => [SomeTrait::class],
                    AnotherParentObject::class => [SomeTrait::class, SecondTrait::class],
                ],
            ],
        ];
    }
}
