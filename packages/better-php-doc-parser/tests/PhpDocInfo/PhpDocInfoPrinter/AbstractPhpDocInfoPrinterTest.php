<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractPhpDocInfoPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfoPrinter
     */
    protected $phpDocInfoPrinter;

    /**
     * @var SmartFileSystem
     */
    protected $smartFileSystem;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
        $this->smartFileSystem = self::$container->get(SmartFileSystem::class);
    }

    protected function createPhpDocInfoFromDocCommentAndNode(string $docComment, Node $node): PhpDocInfo
    {
        $node->setDocComment(new Doc($docComment));

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo === null) {
            throw new ShouldNotHappenException();
        }

        return $phpDocInfo;
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }
}
