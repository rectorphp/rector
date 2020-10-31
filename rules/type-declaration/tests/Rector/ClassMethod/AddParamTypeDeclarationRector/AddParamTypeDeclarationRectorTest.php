<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector;

use Iterator;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ParserInterface;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddParamTypeDeclarationRectorTest extends AbstractRectorTestCase
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
            AddParamTypeDeclarationRector::class => [
                AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => [
                    new AddParamTypeDeclaration(
                        ParentInterfaceWithChangeTypeInterface::class,
                        'process',
                        0,
                        new StringType()
                    ),
                    new AddParamTypeDeclaration(ParserInterface::class, 'parse', 0, new StringType()),
                    new AddParamTypeDeclaration(
                        ClassMetadataFactory::class,
                        'setEntityManager',
                        0,
                        new ObjectType('Doctrine\ORM\EntityManagerInterface')
                    ),
                ],
            ],
        ];
    }
}
