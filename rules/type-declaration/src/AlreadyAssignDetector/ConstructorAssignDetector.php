<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ConstructorAssignDetector extends AbstractAssignDetector
{
    public function isPropertyAssigned(ClassLike $classLike, string $propertyName): bool
    {
        $isAssignedInConstructor = false;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike->stmts, function (Node $node) use (
            $propertyName, &$isAssignedInConstructor
        ): ?int {
            $expr = $this->matchAssignExprToPropertyName($node, $propertyName);
            if (! $expr instanceof Expr) {
                return null;
            }

            // is in constructor?
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName !== MethodName::CONSTRUCT) {
                return null;
            }

            /** @var Assign $assign */
            $assign = $node;
            $isFirstLevelStatement = $assign->getAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT);

            // cannot be nested
            if ($isFirstLevelStatement !== true) {
                return null;
            }

            $isAssignedInConstructor = true;

            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        });

        return $isAssignedInConstructor;
    }
}
