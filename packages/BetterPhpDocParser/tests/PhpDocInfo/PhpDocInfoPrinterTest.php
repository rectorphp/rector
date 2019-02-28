<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Nop;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\HttpKernel\RectorKernel;
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

    /**
     * @dataProvider provideDocFilesForPrint()
     */
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
