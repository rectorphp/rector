<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Nop;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinterSource\DoctrinePropertyClass;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class PhpDocInfoPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
    }

    public function testPrintFormatPreserving(string $docFilePath): void
    {
        $docComment = FileSystem::read($docFilePath);

        $node = new Nop();
        $node->setDocComment(new Doc($docComment));

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

        $this->assertSame(
            $docComment,
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo),
            'Caused in ' . $docFilePath
        );
    }

    public function provideDocFilesForPrint(): Iterator
    {
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc2.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc3.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc4.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc5.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc6.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc7.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc8.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc9.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc10.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc11.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc12.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc13.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc14.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/doc15.txt'];
    }

    public function provideMultiline(): Iterator
    {
        yield [__DIR__ . '/PhpDocInfoPrinterSource/multiline1.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/multiline2.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/multiline3.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/multiline4.txt'];
        yield [__DIR__ . '/PhpDocInfoPrinterSource/multiline5.txt'];
    }

    public function testPrintFormatPreservingDoctrineMultiline(): void
    {
        $docFilePath = __DIR__ . '/PhpDocInfoPrinterSource/multiline6.txt';

        $docComment = FileSystem::read($docFilePath);

        /** @var BuilderFactory $builderFactory */
        $builderFactory = self::$container->get(BuilderFactory::class);
        $property = $builderFactory->property('someProperty')
            ->makePublic()
            ->getNode();

        $property->setDocComment(new Doc($docComment));
        $property->setAttribute(AttributeKey::CLASS_NAME, DoctrinePropertyClass::class);

        // CLASS_NAME attirbutee
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);

        $this->assertSame(
            $docComment,
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo),
            'Caused in ' . $docFilePath
        );
    }

    /**
     * @dataProvider provideDocFilesToEmpty()
     */
    public function testPrintFormatPreservingEmpty(string $docFilePath): void
    {
        $docComment = FileSystem::read($docFilePath);

        $node = new Nop();
        $node->setDocComment(new Doc($docComment));

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

        $this->assertEmpty($this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function provideDocFilesToEmpty(): Iterator
    {
        yield [__DIR__ . '/PhpDocInfoPrinterSource/empty-doc.txt'];
    }
}
