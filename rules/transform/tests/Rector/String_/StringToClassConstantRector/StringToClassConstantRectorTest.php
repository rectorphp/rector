<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\String_\StringToClassConstantRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StringToClassConstantRector::class => [
                StringToClassConstantRector::STRINGS_TO_CLASS_CONSTANTS => [
                    new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT'),
                    new StringToClassConstant('compiler.to_class', 'Yet\AnotherClass', 'class'),
                ],
            ],
        ];
    }
}
