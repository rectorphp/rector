<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\SimplePhpDocNodeTraverser;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\HttpKernel\SimplePhpDocParserKernel;
final class PhpDocNodeTraverserTest extends AbstractKernelTestCase
{
    /**
     * @var string
     */
    private const SOME_DESCRIPTION = 'some description';
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;
    protected function setUp() : void
    {
        $this->bootKernel(SimplePhpDocParserKernel::class);
        $this->phpDocNodeTraverser = $this->getService(PhpDocNodeTraverser::class);
    }
    public function test() : void
    {
        $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode('string'), '', '');
        $phpDocNode = new PhpDocNode([new PhpDocTagNode('@var', $varTagValueNode)]);
        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function (Node $node) : Node {
            if (!$node instanceof VarTagValueNode) {
                return $node;
            }
            $node->description = self::SOME_DESCRIPTION;
            return $node;
        });
        $varTagValueNodes = $phpDocNode->getVarTagValues();
        $this->assertSame(self::SOME_DESCRIPTION, $varTagValueNodes[0]->description);
    }
}
