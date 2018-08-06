<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var Broker
     */
    private $broker;

    public function __construct(
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        DocBlockAnalyzer $docBlockAnalyzer,
        Broker $broker
    ) {
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->broker = $broker;
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

        if ($node instanceof Param) {
            // @todo resolve parents etc.
            return [$node->type->toString()];
        }

        if ($node instanceof Variable) {
            return $this->resolveVariableNode($node, $nodeScope);
        }

        if ($node instanceof Expr) {
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

        return $this->resolveObjectTypesToStrings($type);
    }

    /**
     * @todo make use of recursion
     * @return string[]
     */
    private function resolveObjectTypesToStrings(Type $type): array
    {
        $types = [];

        if ($type instanceof ObjectType) {
            $types[] = $type->getClassName();
        }

        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $type) {
                if ($type instanceof ObjectType) {
                    $types[] = $type->getClassName();
                }
            }
        }

        if ($type instanceof IntersectionType) {
            foreach ($type->getTypes() as $type) {
                if ($type instanceof ObjectType) {
                    $types[] = $type->getClassName();
                }
            }
        }

        return $types;
    }

    /**
     * @return string[]
     */
    private function resolveVariableNode(Variable $variableNode, Scope $nodeScope): array
    {
        $variableName = (string) $variableNode->name;

        if ($nodeScope->hasVariableType($variableName) === TrinaryLogic::createYes()) {
            $type = $nodeScope->getVariableType($variableName);

            // this
            if ($type instanceof ThisType) {
                return $this->classReflectionTypesResolver->resolve($nodeScope->getClassReflection());
            }

            $types = $this->resolveObjectTypesToStrings($type);

            // complete parents
            foreach ($types as $type) {
                $propertyClassReflection = $this->broker->getClass($type);
                $types = array_merge($types, $this->classReflectionTypesResolver->resolve($propertyClassReflection));
            }

            return array_unique($types);
        }

        // get from annotation
        $variableTypes = $this->docBlockAnalyzer->getVarTypes($variableNode);

        foreach ($variableTypes as $i => $type) {
            if (! class_exists($type)) {
                unset($variableTypes[$i]);
                continue;
            }
            $propertyClassReflection = $this->broker->getClass($type);
            $variableTypes = array_merge(
                $variableTypes,
                $this->classReflectionTypesResolver->resolve($propertyClassReflection)
            );
        }

        return array_unique($variableTypes);
    }
}
