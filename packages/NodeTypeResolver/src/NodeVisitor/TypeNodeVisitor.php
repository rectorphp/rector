<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Exception\ShouldNotHappenException;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class TypeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function enterNode(Node $node): void
    {
        if (! $node->hasAttribute(Attribute::SCOPE)) {
            throw new ShouldNotHappenException(sprintf(
                'The "%s" Node attribute should be resolved by "%s" in previous run.',
                Attribute::SCOPE,
                PHPStanScopeResolver::class
            ));
        }

        $this->nodeTypeResolver->resolve($node);
    }
}
