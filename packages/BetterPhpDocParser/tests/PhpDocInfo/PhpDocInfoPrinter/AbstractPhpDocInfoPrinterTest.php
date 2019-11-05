<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\HttpKernel\RectorKernel;
use Rector\Testing\StaticFixtureProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractPhpDocInfoPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfoPrinter
     */
    protected $phpDocInfoPrinter;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
    }

    protected function createPhpDocInfoFromDocCommentAndNode(string $docComment, Node $node): PhpDocInfo
    {
        $node->setDocComment(new Doc($docComment));

        return $this->phpDocInfoFactory->createFromNode($node);
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureProvider::yieldFilesFromDirectory($directory, $suffix);
    }
}
