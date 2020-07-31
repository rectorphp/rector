<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
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
            AddReturnTypeDeclarationRector::class => [
                AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                    'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass' => [
                        'parse' => 'array',
                        'resolve' => 'SomeType',
                        'nullable' => '?SomeType',
                        'clear' => '',
                    ],
                    PHPUnitTestCase::class => [
                        'tearDown' => 'void',
                    ],
                ],
            ],
        ];
    }
}
