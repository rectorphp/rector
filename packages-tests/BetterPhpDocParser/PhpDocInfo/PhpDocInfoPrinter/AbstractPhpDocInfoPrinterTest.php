<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractPhpDocInfoPrinterTest extends AbstractTestCase
{
    protected PhpDocInfoPrinter $phpDocInfoPrinter;

    protected SmartFileSystem $smartFileSystem;

    private PhpDocInfoFactory $phpDocInfoFactory;

    protected function setUp(): void
    {
        $this->boot();

        $this->phpDocInfoFactory = $this->getService(PhpDocInfoFactory::class);
        $this->phpDocInfoPrinter = $this->getService(PhpDocInfoPrinter::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
    }

    protected function createPhpDocInfoFromDocCommentAndNode(string $docComment, Node $node): PhpDocInfo
    {
        $node->setDocComment(new Doc($docComment));
        return $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }
}
