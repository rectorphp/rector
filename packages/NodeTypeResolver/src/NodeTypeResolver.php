<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\TypeAttribute;
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

        // in-code setter injection to drop CompilerPass requirement for 3rd party package install
        if ($perNodeTypeResolver instanceof NodeTypeResolverAwareInterface) {
            $perNodeTypeResolver->setNodeTypeResolver($this);
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(TypeAttribute::SCOPE);
        if ($nodeScope === null) {
            return [];
        }

        // nodes that cannot be resolver by PHPStan
        $nodeClass = get_class($node);
        if (isset($this->perNodeTypeResolvers[$nodeClass])) {
            return $this->perNodeTypeResolvers[$nodeClass]->resolve($node);
        }

        if (! $node instanceof Expr) {
            return [];
        }

        // PHPStan
        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(TypeAttribute::SCOPE);
        $type = $nodeScope->getType($node);

        return $this->typeToStringResolver->resolve($type);
    }
}
