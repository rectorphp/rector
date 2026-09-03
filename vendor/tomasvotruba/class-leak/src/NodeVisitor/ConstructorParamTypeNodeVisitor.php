<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PhpParser\NodeVisitorAbstract;
final class ConstructorParamTypeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private array $paramTypeNames = [];
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        $this->paramTypeNames = [];
        return $nodes;
    }
    /**
     * @return null
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof ClassMethod) {
            return null;
        }
        if ($node->name->toLowerString() !== '__construct') {
            return null;
        }
        foreach ($node->params as $param) {
            if ($param->type === null) {
                continue;
            }
            foreach ($this->resolveTypeNames($param->type) as $typeName) {
                $this->paramTypeNames[] = $typeName;
            }
        }
        return null;
    }
    /**
     * @return string[]
     */
    public function getParamTypeNames(): array
    {
        return array_unique($this->paramTypeNames);
    }
    /**
     * @param Node\Identifier|Name|ComplexType $type
     * @return string[]
     */
    private function resolveTypeNames(Node $type): array
    {
        if ($type instanceof Name) {
            return [$type->toString()];
        }
        if ($type instanceof NullableType) {
            return $this->resolveTypeNames($type->type);
        }
        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            $typeNames = [];
            foreach ($type->types as $innerType) {
                $typeNames = array_merge($typeNames, $this->resolveTypeNames($innerType));
            }
            return $typeNames;
        }
        // builtin Identifier type, e.g. string, int
        return [];
    }
}
