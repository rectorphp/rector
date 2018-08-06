<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    public function __construct(TypeToStringResolver $typeToStringResolver)
    {
        $this->typeToStringResolver = $typeToStringResolver;
    }

    public function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
    {
        foreach ($perNodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->perNodeTypeResolvers[$nodeClass] = $perNodeTypeResolver;
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if ($nodeScope === null) {
            return [];
        }

        if ($node instanceof Expr && ! $node instanceof Variable) {
            return $this->resolveExprNode($node);
        }

        $nodeClass = get_class($node);
        if (isset($this->perNodeTypeResolvers[$nodeClass])) {
            return $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function resolveExprNode(Expr $exprNode): array
    {
        /** @var Scope $nodeScope */
        $nodeScope = $exprNode->getAttribute(Attribute::SCOPE);

        $type = $nodeScope->getType($exprNode);

        return $this->typeToStringResolver->resolve($type);
    }
}
