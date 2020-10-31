<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector;

use Iterator;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentAdderRectorTest extends AbstractRectorTestCase
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
            ArgumentAdderRector::class => [
                ArgumentAdderRector::ADDED_ARGUMENTS => [
                    // covers https://github.com/rectorphp/rector/issues/4267
                    new ArgumentAdder(
                        SomeContainerBuilder::class,
                        'sendResetLinkResponse',
                        0,
                        'request',
                        null,
                        'Illuminate\Http\Illuminate\Http'
                    ),

                    new ArgumentAdder(SomeContainerBuilder::class, 'compile', 0, 'isCompiled', false),
                    new ArgumentAdder(SomeContainerBuilder::class, 'addCompilerPass', 2, 'priority', 0, 'int'),

                    // scoped
                    new ArgumentAdder(
                        SomeParentClient::class,
                        'submit',
                        2,
                        'serverParameters',
                        [],
                        'array',
                        ArgumentAdderRector::SCOPE_PARENT_CALL
                    ),
                    new ArgumentAdder(
                        SomeParentClient::class,
                        'submit',
                        2,
                        'serverParameters',
                        [],
                        'array',
                        ArgumentAdderRector::SCOPE_CLASS_METHOD
                    ),
                ],
            ],
        ];
    }
}
