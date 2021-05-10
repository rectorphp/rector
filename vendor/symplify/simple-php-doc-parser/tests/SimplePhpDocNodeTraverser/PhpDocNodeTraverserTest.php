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
final class PhpDocNodeTraverserTest extends \RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
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
        $this->bootKernel(\RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\HttpKernel\SimplePhpDocParserKernel::class);
        $this->phpDocNodeTraverser = $this->getService(\RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::class);
    }
    public function test() : void
    {
        $varTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode(new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('string'), '', '');
        $phpDocNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode([new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@var', $varTagValueNode)]);
        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function (\PHPStan\PhpDocParser\Ast\Node $node) : Node {
            if (!$node instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
                return $node;
            }
            $node->description = self::SOME_DESCRIPTION;
            return $node;
        });
        $varTagValueNodes = $phpDocNode->getVarTagValues();
        $this->assertSame(self::SOME_DESCRIPTION, $varTagValueNodes[0]->description);
    }
}
