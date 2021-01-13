<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\AnotherPropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\Class_\SomeEntityClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\DoctrinePropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\ManyToPropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\RoutePropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\SinglePropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\TableClass;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MultilineTest extends AbstractPhpDocInfoPrinterTest
{
    /**
     * @dataProvider provideData()
     * @dataProvider provideDataForProperty()
     * @dataProvider provideDataClass()
     */
    public function test(string $docFilePath, Node $node): void
    {
        $docComment = $this->smartFileSystem->readFile($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, $node);

        $fileInfo = new SmartFileInfo($docFilePath);
        $relativeFilePathFromCwd = $fileInfo->getRelativeFilePathFromCwd();

        $printedPhpDocInfo = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        $this->assertSame($docComment, $printedPhpDocInfo, $relativeFilePathFromCwd);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/Multiline/multiline1.txt', new Nop()];
        yield [__DIR__ . '/Source/Multiline/multiline2.txt', new Nop()];
        yield [__DIR__ . '/Source/Multiline/multiline3.txt', new Nop()];
        yield [__DIR__ . '/Source/Multiline/multiline4.txt', new Nop()];
        yield [__DIR__ . '/Source/Multiline/multiline5.txt', new Nop()];
    }

    public function provideDataClass(): Iterator
    {
        yield [__DIR__ . '/Source/Class_/some_entity_class.txt', new Class_(SomeEntityClass::class)];

        yield [__DIR__ . '/Source/Multiline/table.txt', new Class_(TableClass::class)];
    }

    public function provideDataForProperty(): Iterator
    {
        $property = $this->createPublicPropertyUnderClass('manyTo', ManyToPropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/many_to.txt', $property];

        $property = $this->createPublicPropertyUnderClass('anotherProperty', AnotherPropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/assert_serialize.txt', $property];

        $property = $this->createPublicPropertyUnderClass('anotherSerializeSingleLine', SinglePropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/assert_serialize_single_line.txt', $property];

        $property = $this->createPublicPropertyUnderClass('someProperty', DoctrinePropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/multiline6.txt', $property];

        $property = $this->createMethodUnderClass('someMethod', RoutePropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/route_property.txt', $property];
    }

    private function createPublicPropertyUnderClass(string $name, string $class): Property
    {
        $builderFactory = new BuilderFactory();

        $propertyBuilder = $builderFactory->property($name);
        $propertyBuilder->makePublic();

        $property = $propertyBuilder->getNode();
        $property->setAttribute(AttributeKey::CLASS_NAME, $class);

        return $property;
    }

    private function createMethodUnderClass(string $name, string $class): ClassMethod
    {
        $builderFactory = new BuilderFactory();

        $methodBuilder = $builderFactory->method($name);
        $methodBuilder->makePublic();

        $classMethod = $methodBuilder->getNode();
        $classMethod->setAttribute(AttributeKey::CLASS_NAME, $class);

        return $classMethod;
    }
}
