<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
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
        Name::class,
        FullyQualified::class,
        Class_::class,
        New_::class,
        MethodCall::class
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
