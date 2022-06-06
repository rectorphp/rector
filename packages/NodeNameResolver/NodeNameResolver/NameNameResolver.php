<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeNameResolverInterface<Name>
 */
final class NameNameResolver implements NodeNameResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver\FuncCallNameResolver
     */
    private $funcCallNameResolver;
    public function __construct(FuncCallNameResolver $funcCallNameResolver)
    {
        $this->funcCallNameResolver = $funcCallNameResolver;
    }
    public function getNode() : string
    {
        return Name::class;
    }
    /**
     * @param Name $node
     */
    public function resolve(Node $node) : ?string
    {
        // possible function parent
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof FuncCall) {
            return $this->funcCallNameResolver->resolve($parent);
        }
        $resolvedName = $node->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedName instanceof FullyQualified) {
            return $resolvedName->toString();
        }
        return $node->toString();
    }
}
