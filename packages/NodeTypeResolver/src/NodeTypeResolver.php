<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NodeTypeResolver
{
    /**
     * @var PerNodeTypeResolverInterface[]
     */
    private $perNodeTypeResolvers = [];

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
        if ($node instanceof Variable) {
            $nodeTypes = $this->perNodeTypeResolvers[Variable::class]->resolve($node);
            if (count($nodeTypes)) {
                return $nodeTypes;
            }
        }


        if ($node instanceof Expr) {
            return $this->resolveExprNode($node);
        }

        $nodeClass = get_class($node);
        if (! isset($this->perNodeTypeResolvers[$nodeClass])) {
            return [];
        }

        $nodeTypes = $this->perNodeTypeResolvers[$nodeClass]->resolve($node);

        return $this->cleanPreSlashes($nodeTypes);
    }

    /**
     * @return string[]
     */
    private function resolveExprNode(Expr $exprNode): array
    {
        /** @var Scope $nodeScope */
        $nodeScope = $exprNode->getAttribute(Attribute::SCOPE);

        $types = [];
        // @todo decouple - resolve $this
        if ($exprNode instanceof Variable && $exprNode->name === 'this') {
            $types[] = $nodeScope->getClassReflection()->getName();
            $types = array_merge($types, $nodeScope->getClassReflection()->getParentClassesNames());
            foreach ($nodeScope->getClassReflection()->getInterfaces() as $classReflection) {
                $types[] = $classReflection->getName();
            }

            return $types;
        }

        $type = $nodeScope->getType($exprNode);

        return $this->resolveObjectTypesToStrings($type);
    }

    /**
     * "\FqnType" => "FqnType"
     *
     * @param string[] $nodeTypes
     * @return string[]
     */
    private function cleanPreSlashes(array $nodeTypes): array
    {
        foreach ($nodeTypes as $key => $nodeType) {
            // filter out non-type values
            if (! is_string($nodeType)) {
                continue;
            }
            $nodeTypes[$key] = ltrim($nodeType, '\\');
        }

        return $nodeTypes;
    }

    /**
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
                // @todo recursion
                if ($type instanceof ObjectType) {
                    $types[] = $type->getClassName();
                }
            }
        }

        if ($type instanceof IntersectionType) {
            foreach ($type->getTypes() as $type) {
                // @todo recursion
                if ($type instanceof ObjectType) {
                    $types[] = $type->getClassName();
                }
            }
        }

        return $types;
    }
}
