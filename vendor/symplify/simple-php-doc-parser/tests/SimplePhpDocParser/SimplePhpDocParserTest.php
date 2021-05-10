<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\SimplePhpDocParser;

use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\SimplePhpDocParser;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\HttpKernel\SimplePhpDocParserKernel;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\ValueObject\Ast\PhpDoc\SimplePhpDocNode;
use Symplify\SmartFileSystem\SmartFileInfo;
final class SimplePhpDocParserTest extends AbstractKernelTestCase
{
    /**
     * @var SimplePhpDocParser
     */
    private $simplePhpDocParser;
    protected function setUp() : void
    {
        $this->bootKernel(SimplePhpDocParserKernel::class);
        $this->simplePhpDocParser = $this->getService(SimplePhpDocParser::class);
    }
    public function testVar() : void
    {
        $smartFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/var_int.txt');
        $phpDocNode = $this->simplePhpDocParser->parseDocBlock($smartFileInfo->getContents());
        $this->assertInstanceOf(SimplePhpDocNode::class, $phpDocNode);
        $varTagValues = $phpDocNode->getVarTagValues();
        $this->assertCount(1, $varTagValues);
    }
    public function testParam() : void
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
