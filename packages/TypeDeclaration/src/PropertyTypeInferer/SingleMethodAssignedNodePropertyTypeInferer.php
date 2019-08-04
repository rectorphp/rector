<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\StaticTypeToStringResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class SingleMethodAssignedNodePropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

    public function __construct(
        NameResolver $nameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeToStringResolver $staticTypeToStringResolver
    ) {
        $this->nameResolver = $nameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var Node\Stmt\Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        $classMethod = $class->getMethod('__construct');
        if ($classMethod === null) {
            return [];
        }

        $propertyName = $this->nameResolver->resolve($property);

        $assignedNode = $this->resolveAssignedNodeToProperty($classMethod, $propertyName);
        if ($assignedNode === null) {
            return [];
        }

        $nodeStaticType = $this->nodeTypeResolver->getNodeStaticType($assignedNode);

        $stringTypes = $this->staticTypeToStringResolver->resolveObjectType($nodeStaticType);
        if ($stringTypes === []) {
            return [];
        }

        return array_unique($stringTypes);
    }

    public function getPriority(): int
    {
        return 750;
    }

    private function resolveAssignedNodeToProperty(ClassMethod $classMethod, string $propertyName): ?Expr
    {
        $assignedNode = null;
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $propertyName,
            &$assignedNode
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->nameResolver->isName($node->var, $propertyName)) {
                return null;
            }

            $assignedNode = $node->expr;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $assignedNode;
    }
}
