<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\String_\StringToClassConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\String_\StringToClassConstantRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StringToClassConstantRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StringToClassConstantRector::class => [
                StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => [
                    'compiler.post_dump' => ['Yet\AnotherClass', 'CONSTANT'],
                    'compiler.to_class' => ['Yet\AnotherClass', 'class'],
                ],
            ],
        ];
    }
}
