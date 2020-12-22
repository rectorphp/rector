<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector;

use Iterator;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
        $arrayType = new ArrayType(new MixedType(), new MixedType());

        $nullableObjectUnionType = new UnionType([new ObjectType('SomeType'), new NullType()]);

        return [
            AddReturnTypeDeclarationRector::class => [
                AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => [
                    new AddReturnTypeDeclaration(
                        'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\Fixture',
                        'parse',
                        $arrayType
                    ),
                    new AddReturnTypeDeclaration(
                        'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\Fixture',
                        'resolve',
                        new ObjectType('SomeType')
                    ),
                    new AddReturnTypeDeclaration(
                        'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\Fixture',
                        'nullable',
                        $nullableObjectUnionType
                    ),
                    new AddReturnTypeDeclaration(
                        'Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\RemoveReturnType',
                        'clear',
                        new MixedType()
                    ),
                    new AddReturnTypeDeclaration(PHPUnitTestCase::class, 'tearDown', new VoidType()),
                ],
            ],
        ];
    }
}
