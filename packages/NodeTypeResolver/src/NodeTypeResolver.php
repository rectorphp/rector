<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

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
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @param PerNodeTypeResolverInterface[] $perNodeTypeResolvers
     */
    public function __construct(
        TypeToStringResolver $typeToStringResolver,
        Broker $broker,
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        NameResolver $nameResolver,
        array $perNodeTypeResolvers
    ) {
        $this->typeToStringResolver = $typeToStringResolver;
        $this->broker = $broker;
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;

        foreach ($perNodeTypeResolvers as $perNodeTypeResolver) {
            $this->addPerNodeTypeResolver($perNodeTypeResolver);
        }
        $this->nameResolver = $nameResolver;
    }

    public function isType(Node $node, string $type): bool
    {
        $nodeTypes = $this->getTypes($node);

        // fnmatch support
        if (Strings::contains($type, '*')) {
            foreach ($nodeTypes as $nodeType) {
                if (fnmatch($type, $nodeType, FNM_NOESCAPE)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($type, $nodeTypes, true);
    }

    /**
     * @return string[]
     */
    public function getTypes(Node $node): array
    {
        // @todo should be resolved by NodeTypeResolver internally
        if ($node instanceof MethodCall || $node instanceof PropertyFetch || $node instanceof ArrayDimFetch) {
            return $this->resolve($node->var);
        }

        return $this->resolve($node);
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if ($node instanceof ClassMethod || $node instanceof ClassConst) {
            return $this->resolveClassNode($node);
        }

        if ($node instanceof StaticCall) {
            return $this->resolveStaticCall($node);
        }

        if ($node instanceof ClassConstFetch) {
            return $this->resolve($node->class);
        }

        if ($node instanceof Cast) {
            return $this->resolve($node->expr);
        }

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

            if (class_exists($type)) {
                $types += $this->classReflectionTypesResolver->resolve($this->broker->getClass($type));
            }
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
     * @param ClassConst|ClassMethod $node
     * @return string[]
     */
    private function resolveClassNode(Node $node): array
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            throw new ShouldNotHappenException();
        }

        return $this->resolve($classNode);
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
        $type = $nodeScope->getType($node);

        $typesInStrings = $this->typeToStringResolver->resolve($type);

        // hot fix for phpstan not resolving chain method calls
        if ($node instanceof MethodCall && ! $typesInStrings) {
            return $this->resolveFirstTypes($node->var);
        }

        return $typesInStrings;
    }

    /**
     * @return string[]
     */
    private function resolveStaticCall(StaticCall $staticCall): array
    {
        $classTypes = $this->resolve($staticCall->class);
        $methodName = $this->nameResolver->resolve($staticCall->name);

        // fallback
        if (! is_string($methodName)) {
            return $this->resolve($staticCall->class);
        }

        foreach ($classTypes as $classType) {
            if (! method_exists($classType, $methodName)) {
                continue;
            }

            /** @var Scope $nodeScope */
            $nodeScope = $staticCall->getAttribute(Attribute::SCOPE);
            $type = $nodeScope->getType($staticCall);
            if ($type instanceof ObjectType) {
                return [$type->getClassName()];
            }
        }

        return $this->resolve($staticCall->class);
    }
}
