<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\HttpKernel\SimplePhpDocParserKernel;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey;
final class ParentConnectingPhpDocNodeVisitorTest extends \RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;
    protected function setUp() : void
    {
        $this->bootKernel(\RectorPrefix20210510\Symplify\SimplePhpDocParser\Tests\HttpKernel\SimplePhpDocParserKernel::class);
        $this->phpDocNodeTraverser = $this->getService(\RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::class);
        /** @var ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor */
        $parentConnectingPhpDocNodeVisitor = $this->getService(\RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor::class);
        $this->phpDocNodeTraverser->addPhpDocNodeVisitor($parentConnectingPhpDocNodeVisitor);
    }
    public function testChildNode() : void
    {
        $phpDocNode = $this->createPhpDocNode();
        $this->phpDocNodeTraverser->traverse($phpDocNode);
        $phpDocChildNode = $phpDocNode->children[0];
        $this->assertInstanceOf(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode::class, $phpDocChildNode);
        $childParent = $phpDocChildNode->getAttribute(\RectorPrefix20210510\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT);
        $this->assertSame($phpDocNode, $childParent);
    }
    public function testTypeNode() : void
    {
        $phpDocNode = $this->createPhpDocNode();
        $this->phpDocNodeTraverser->traverse($phpDocNode);
        /** @var PhpDocTagNode $phpDocChildNode */
        $phpDocChildNode = $phpDocNode->children[0];
        $returnTagValueNode = $phpDocChildNode->value;
        $this->assertInstanceOf(\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class, $returnTagValueNode);
        /** @var ReturnTagValueNode $returnTagValueNode */
        $returnParent = $returnTagValueNode->getAttribute(\RectorPrefix20210510\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT);
        $this->assertSame($phpDocChildNode, $returnParent);
        $returnTypeParent = $returnTagValueNode->type->getAttribute(\RectorPrefix20210510\Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey::PARENT);
        $this->assertSame($returnTagValueNode, $returnTypeParent);
    }
    private function createPhpDocNode() : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode
    {
        $returnTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode(new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('string'), '');
        return new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode([new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@return', $returnTagValueNode), new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode('some text')]);
    }
}
