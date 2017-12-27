<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class TypeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var string[]
     */
    private $nodesGraduallyToSkip = [
        Node\Name::class,
        Node\Name\FullyQualified::class,
        Node\Stmt\Class_::class,
        Node\Expr\New_::class,
//        Variable::class,
//        Property::class
    ];

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function enterNode(Node $node): void
    {
        if (in_array(get_class($node), $this->nodesGraduallyToSkip, true)) {
            return;
        }

        $this->nodeTypeResolver->resolve($node);
    }
}
