<?php

declare(strict_types=1);

namespace Rector\SimplePhpDocParser\Tests\SimplePhpDocParser;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\SimplePhpDocParser\SimplePhpDocParser;
use Rector\SimplePhpDocParser\ValueObject\Ast\PhpDoc\SimplePhpDocNode;
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

    public function testVar(): void
    {
        $smartFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/var_int.txt');

        $phpDocNode = $this->simplePhpDocParser->parseDocBlock($smartFileInfo->getContents());
        $this->assertInstanceOf(SimplePhpDocNode::class, $phpDocNode);

        $varTagValues = $phpDocNode->getVarTagValues();
        $this->assertCount(1, $varTagValues);
    }

    public function testParam(): void
    {
        $smartFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/param_string_name.txt');

        $phpDocNode = $this->simplePhpDocParser->parseDocBlock($smartFileInfo->getContents());
        $this->assertInstanceOf(SimplePhpDocNode::class, $phpDocNode);

        // DX friendly
        $paramType = $phpDocNode->getParamType('name');
        $withDollarParamType = $phpDocNode->getParamType('$name');

        $this->assertSame($paramType, $withDollarParamType);
    }
}
