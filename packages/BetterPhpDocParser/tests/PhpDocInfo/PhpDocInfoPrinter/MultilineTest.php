<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\DoctrinePropertyClass;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MultilineTest extends AbstractPhpDocInfoPrinterTest
{
//    /**
//     * @dataProvider provideData()
//     */
//    public function test(string $docFilePath): void
//    {
//        $docComment = FileSystem::read($docFilePath);
//        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, new Nop());
//
//        $this->assertSame(
//            $docComment,
//            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo),
//            'Caused in ' . $docFilePath
//        );
//    }
//
//    public function provideData(): Iterator
//    {
//        yield [__DIR__ . '/Source/Multiline/multiline1.txt'];
//        yield [__DIR__ . '/Source/Multiline/multiline2.txt'];
//        yield [__DIR__ . '/Source/Multiline/multiline3.txt'];
//        yield [__DIR__ . '/Source/Multiline/multiline4.txt'];
//        yield [__DIR__ . '/Source/Multiline/multiline5.txt'];
//    }

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
