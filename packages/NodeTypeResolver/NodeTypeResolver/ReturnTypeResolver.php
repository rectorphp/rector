<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\VoidType;
use RectorPrefix20220606\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements NodeTypeResolverInterface<Return_>
 */
final class ReturnTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @required
     */
    public function autowire(NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function resolve(Node $node) : Type
    {
        if ($node->expr === null) {
            return new VoidType();
        }
        return $this->nodeTypeResolver->getType($node->expr);
    }
}
