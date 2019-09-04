<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class SingleMethodAssignedNodePropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        $classMethod = $class->getMethod('__construct');
        if ($classMethod === null) {
            return [];
        }

        $propertyName = $this->nameResolver->getName($property);

        $assignedNode = $this->resolveAssignedNodeToProperty($classMethod, $propertyName);
        if ($assignedNode === null) {
            return [];
        }

        $nodeStaticType = $this->nodeTypeResolver->getStaticType($assignedNode);
        if ($nodeStaticType === null) {
            return [];
        }

        $stringTypes = $this->staticTypeMapper->mapPHPStanTypeToStrings($nodeStaticType);
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
