<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\AnotherPropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\DoctrinePropertyClass;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\SinglePropertyClass;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MultilineTest extends AbstractPhpDocInfoPrinterTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $docFilePath, Node $node): void
    {
        $docComment = FileSystem::read($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, $node);

        $this->assertSame(
            $docComment,
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo),
            'Caused in ' . $docFilePath
        );
    }

    public function provideData(): Iterator
    {
        $nopNode = new Nop();
        yield [__DIR__ . '/Source/Multiline/multiline1.txt', $nopNode];
        yield [__DIR__ . '/Source/Multiline/multiline2.txt', $nopNode];
        yield [__DIR__ . '/Source/Multiline/multiline3.txt', $nopNode];
        yield [__DIR__ . '/Source/Multiline/multiline4.txt', $nopNode];
        yield [__DIR__ . '/Source/Multiline/multiline5.txt', $nopNode];

        $builderFactory = new BuilderFactory();

        $propertyBuilder = $builderFactory->property('anotherProperty');
        $propertyBuilder->makePublic();
        $property = $propertyBuilder->getNode();
        $property->setAttribute(AttributeKey::CLASS_NAME, AnotherPropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/assert_serialize.txt', $property];

        $propertyBuilder = $builderFactory->property('anotherSerializeSingleLine');
        $propertyBuilder->makePublic();
        $property = $propertyBuilder->getNode();
        $property->setAttribute(AttributeKey::CLASS_NAME, SinglePropertyClass::class);
        yield [__DIR__ . '/Source/Multiline/assert_serialize_single_line.txt', $property];
    }

    public function testDoctrine(): void
    {
        $docFilePath = __DIR__ . '/Source/Multiline/multiline6.txt';
        $docComment = FileSystem::read($docFilePath);

        $property = $this->createDoctrineProperty();
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, $property);

        $this->assertSame(
            $docComment,
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo),
            'Caused in ' . $docFilePath
        );
    }

    private function createDoctrineProperty(): Property
    {
        /** @var BuilderFactory $builderFactory */
        $builderFactory = self::$container->get(BuilderFactory::class);

        $propertyBuilder = $builderFactory->property('someProperty');
        $propertyBuilder->makePublic();

        $property = $propertyBuilder->getNode();
        $property->setAttribute(AttributeKey::CLASS_NAME, DoctrinePropertyClass::class);

        return $property;
    }
}
