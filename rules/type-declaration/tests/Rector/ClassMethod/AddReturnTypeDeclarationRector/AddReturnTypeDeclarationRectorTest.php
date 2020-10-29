<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddReturnTypeDeclarationRector::class => [
                AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => [
                    new AddReturnTypeDeclaration(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass',
                        'parse',
                        'array'
                    ),
                    new AddReturnTypeDeclaration(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass',
                        'resolve',
                        'SomeType'
                    ),
                    new AddReturnTypeDeclaration(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass',
                        'nullable',
                        '?SomeType'
                    ),
                    new AddReturnTypeDeclaration(
                        'Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\RemoveReturnType',
                        'clear',
                        ''
                    ),
                    new AddReturnTypeDeclaration(PHPUnitTestCase::class, 'tearDown', 'void'),
                ],
            ],
        ];
    }
}
