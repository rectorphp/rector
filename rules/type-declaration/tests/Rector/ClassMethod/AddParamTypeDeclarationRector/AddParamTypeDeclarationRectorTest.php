<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ParserInterface;
use Rector\TypeDeclaration\ValueObject\ParameterTypehint;
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddParamTypeDeclarationRector::class => [
                AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => [
                    new ParameterTypehint(ParentInterfaceWithChangeTypeInterface::class, 'process', 0, 'string'),
                    new ParameterTypehint(ParserInterface::class, 'parse', 0, 'string'),
                    new ParameterTypehint(
                        ClassMetadataFactory::class,
                        'setEntityManager',
                        0,
                        'Doctrine\ORM\EntityManagerInterface'
                    ),
                ],
            ],
        ];
    }
}
