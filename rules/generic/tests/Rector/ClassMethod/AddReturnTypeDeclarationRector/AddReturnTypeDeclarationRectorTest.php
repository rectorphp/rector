<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;
use Rector\Generic\ValueObject\MethodReturnType;
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
                AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => [
                    new MethodReturnType(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass',
                        'parse',
                        'array'
                    ),
                    new MethodReturnType(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass',
                        'resolve',
                        'SomeType'
                    ),
                    new MethodReturnType(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass',
                        'nullable',
                        '?SomeType'
                    ),
                    new MethodReturnType(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\RemoveReturnType',
                        'clear',
                        ''
                    ),
                    new MethodReturnType(PHPUnitTestCase::class, 'tearDown', 'void'),
                ],
            ],
        ];
    }
}
