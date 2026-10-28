<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\NodeVisitor\CloningVisitor;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocNodeMapperTest
 */
final class PhpDocNodeMapper
{
    /**
     * @readonly
     */
    private NodeTraverser $phpDocNodeTraverser;
    public function __construct(ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor)
    {
        $this->phpDocNodeTraverser = new NodeTraverser([new CloningVisitor(), $parentConnectingPhpDocNodeVisitor]);
    }
    /**
     * Returns a clone to change; the format-preserving printer compares it with the original node
     */
    public function transform(PhpDocNode $phpDocNode): PhpDocNode
    {
        [$clonedPhpDocNode] = $this->phpDocNodeTraverser->traverse([$phpDocNode]);
        Assert::isInstanceOf($clonedPhpDocNode, PhpDocNode::class);
        return $clonedPhpDocNode;
    }
}
