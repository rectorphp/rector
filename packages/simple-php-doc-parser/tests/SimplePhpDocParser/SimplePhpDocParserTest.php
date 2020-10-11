<?php

declare(strict_types=1);

namespace Rector\SimplePhpDocParser\Tests\SimplePhpDocParser;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\SimplePhpDocParser\SimplePhpDocParser;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimplePhpDocParserTest extends AbstractKernelTestCase
{
    /**
     * @var SimplePhpDocParser
     */
    private $simplePhpDocParser;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->simplePhpDocParser = self::$container->get(SimplePhpDocParser::class);
    }

    public function test(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/basic_doc.txt');

        $phpDocNode = $this->simplePhpDocParser->parseDocBlock($fileInfo->getContents());

        $varTagValues = $phpDocNode->getVarTagValues();
        $this->assertCount(1, $varTagValues);
    }
}
