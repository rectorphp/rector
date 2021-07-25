<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\Matcher\PropertyAssignMatcher;
use Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ConstructorAssignDetector
{
    /**
     * @var string
     */
    private const IS_FIRST_LEVEL_STATEMENT = 'first_level_stmt';

    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private PropertyAssignMatcher $propertyAssignMatcher,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer
    ) {
    }

    public function isPropertyAssigned(ClassLike $classLike, string $propertyName): bool
    {
        $initializeClassMethods = $this->matchInitializeClassMethod($classLike);
        if ($initializeClassMethods === []) {
            return false;
        }

        $isAssignedInConstructor = false;

        $this->decorateFirstLevelStatementAttribute($initializeClassMethods);

        foreach ($initializeClassMethods as $initializeClassMethod) {
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $initializeClassMethod->stmts, function (
                Node $node
            ) use ($propertyName, &$isAssignedInConstructor): ?int {
                $expr = $this->matchAssignExprToPropertyName($node, $propertyName);
                if (! $expr instanceof Expr) {
                    return null;
                }

                /** @var Assign $assign */
                $assign = $node;
                $isFirstLevelStatement = $assign->getAttribute(self::IS_FIRST_LEVEL_STATEMENT);

                // cannot be nested
                if ($isFirstLevelStatement !== true) {
                    return null;
                }

                $isAssignedInConstructor = true;

                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            });
        }

        return $isAssignedInConstructor;
    }

    private function matchAssignExprToPropertyName(Node $node, string $propertyName): ?Expr
    {
        if (! $node instanceof Assign) {
            return null;
        }

        return $this->propertyAssignMatcher->matchPropertyAssignExpr($node, $propertyName);
    }

    /**
     * @param ClassMethod[] $classMethods
     */
    private function decorateFirstLevelStatementAttribute(array $classMethods): void
    {
        foreach ($classMethods as $classMethod) {
            foreach ((array) $classMethod->stmts as $methodStmt) {
                $methodStmt->setAttribute(self::IS_FIRST_LEVEL_STATEMENT, true);

                if ($methodStmt instanceof Expression) {
                    $methodStmt->expr->setAttribute(self::IS_FIRST_LEVEL_STATEMENT, true);
                }
            }
        }
    }

    /**
     * @return ClassMethod[]
     */
    private function matchInitializeClassMethod(ClassLike $classLike): array
    {
        $initializingClassMethods = [];

        $constructClassMethod = $classLike->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            $initializingClassMethods[] = $constructClassMethod;
        }

        $testCaseObjectType = new ObjectType('PHPUnit\Framework\TestCase');
        if ($this->nodeTypeResolver->isObjectType($classLike, $testCaseObjectType)) {
            $setUpClassMethod = $classLike->getMethod(MethodName::SET_UP);
            if ($setUpClassMethod instanceof ClassMethod) {
                $initializingClassMethods[] = $setUpClassMethod;
            }

            $setUpBeforeClassMethod = $classLike->getMethod(MethodName::SET_UP_BEFORE_CLASS);
            if ($setUpBeforeClassMethod instanceof ClassMethod) {
                $initializingClassMethods[] = $setUpBeforeClassMethod;
            }
        }

        foreach ($classLike->getMethods() as $classMethod) {
            if (! $this->autowiredClassMethodOrPropertyAnalyzer->detect($classMethod)) {
                continue;
            }

            $initializingClassMethods[] = $classMethod;
        }

        return $initializingClassMethods;
    }
}
