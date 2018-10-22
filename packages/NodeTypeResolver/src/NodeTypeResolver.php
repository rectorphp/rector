<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

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

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @param PerNodeTypeResolverInterface[] $perNodeTypeResolvers
     */
    public function __construct(
        TypeToStringResolver $typeToStringResolver,
        Broker $broker,
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        array $perNodeTypeResolvers
    ) {
        $this->typeToStringResolver = $typeToStringResolver;
        $this->broker = $broker;
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;

        foreach ($perNodeTypeResolvers as $perNodeTypeResolver) {
            $this->addPerNodeTypeResolver($perNodeTypeResolver);
        }
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        $types = $this->resolveFirstTypes($node);
        if ($types === []) {
            return $types;
        }

        // complete parent types - parent classes, interfaces and traits
        foreach ($types as $i => $type) {
            // remove scalar types and other non-existing ones
            if ($type === 'null' || $type === null) {
                unset($types[$i]);
                continue;
            }

            $types += $this->classReflectionTypesResolver->resolve($this->broker->getClass($type));
        }

        return $types;
    }

    private function addPerNodeTypeResolver(PerNodeTypeResolverInterface $perNodeTypeResolver): void
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
    private function resolveFirstTypes(Node $node): array
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
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
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        $type = $nodeScope->getType($node);

        $typesInStrings = $this->typeToStringResolver->resolve($type);

        // hot fix for phpstan not resolving chain method calls
        if ($node instanceof MethodCall && ! $typesInStrings) {
            return $this->resolveFirstTypes($node->var);
        }

        return $typesInStrings;
    }
}
