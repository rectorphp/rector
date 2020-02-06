<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\String_\StringToClassConstantRector;

use Iterator;
use Rector\Core\Rector\String_\StringToClassConstantRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class StringToClassConstantRectorTest extends AbstractRectorTestCase
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
            StringToClassConstantRector::class => [
                '$stringsToClassConstants' => [
                    'compiler.post_dump' => ['Yet\AnotherClass', 'CONSTANT'],
                    'compiler.to_class' => ['Yet\AnotherClass', 'class'],
                ],
            ],
        ];
    }
}
