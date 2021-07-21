<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeTraverser;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class PropertyFetchByMethodAnalyzer
{
    /**
     * @var array<class-string<Stmt>>
     */
    private const SCOPE_CHANGING_NODE_TYPES = [Do_::class, While_::class, If_::class, Else_::class];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
    }

    /**
     * @param string[] $propertyNames
     * @return array<string, string[]>
     */
    public function collectPropertyFetchByMethods(Class_ $class, array $propertyNames): array
    {
        $propertyUsageByMethods = [];

        foreach ($propertyNames as $propertyName) {
            if ($this->isPropertyHasDefaultValue($class, $propertyName)) {
                continue;
            }

            foreach ($class->getMethods() as $classMethod) {
                // assigned in constructor injection → skip
                if ($this->isInConstructWithPropertyChanging($classMethod, $propertyName)) {
                    return [];
                }

                if ($this->isContainsLocalPropertyFetchNameOrPropertyChangingInMultipleMethodCalls(
                    $classMethod,
                    $propertyName
                )) {
                    continue;
                }

                if (! $this->isPropertyChanging($classMethod, $propertyName)) {
                    continue;
                }

                $classMethodName = $this->nodeNameResolver->getName($classMethod);
                $propertyUsageByMethods[$propertyName][] = $classMethodName;
            }
        }

        return $propertyUsageByMethods;
    }

    private function isContainsLocalPropertyFetchNameOrPropertyChangingInMultipleMethodCalls(
        ClassMethod $classMethod,
        string $propertyName
    ): bool
    {
        if (! $this->propertyFetchAnalyzer->containsLocalPropertyFetchName($classMethod, $propertyName)) {
            return true;
        }

        return $this->isPropertyChangingInMultipleMethodCalls($classMethod, $propertyName);
    }

    private function isPropertyHasDefaultValue(Class_ $class, string $propertyName): bool
    {
        $property = $class->getProperty($propertyName);
        return $property instanceof Property && $property->props[0]->default;
    }

    private function isInConstructWithPropertyChanging(ClassMethod $classMethod, string $propertyName): bool
    {
        if (! $this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return false;
        }
        return $this->isPropertyChanging($classMethod, $propertyName);
    }

    /**
     * Covers https://github.com/rectorphp/rector/pull/2558#discussion_r363036110
     */
    private function isPropertyChangingInMultipleMethodCalls(ClassMethod $classMethod, string $propertyName): bool
    {
        $isPropertyChanging = false;
        $isPropertyReadInIf = false;
        $isIfFollowedByAssign = false;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use (
                &$isPropertyChanging,
                $propertyName,
                &$isPropertyReadInIf,
                &$isIfFollowedByAssign
            ): ?int {
                if ($isPropertyReadInIf) {
                    if (! $this->propertyFetchAnalyzer->isLocalPropertyOfNames($node, [$propertyName])) {
                        return null;
                    }

                    $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
                    if ($parentNode instanceof Assign && $parentNode->var === $node) {
                        $isIfFollowedByAssign = true;
                    }
                }

                if (! $this->isScopeChangingNode($node)) {
                    return null;
                }

                $isPropertyReadInIf = $this->verifyPropertyReadInIf($isPropertyReadInIf, $node, $propertyName);
                $isPropertyChanging = $this->isPropertyChanging($node, $propertyName);

                if (! $isPropertyChanging) {
                    return null;
                }

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $isPropertyChanging || $isIfFollowedByAssign || $isPropertyReadInIf;
    }

    private function verifyPropertyReadInIf(?bool $isPropertyReadInIf, Node $node, string $propertyName): ?bool
    {
        if ($node instanceof If_) {
            $isPropertyReadInIf = $this->refactorIf($node, $propertyName);
        }

        return $isPropertyReadInIf;
    }

    private function isScopeChangingNode(Node $node): bool
    {
        foreach (self::SCOPE_CHANGING_NODE_TYPES as $type) {
            if (is_a($node, $type, true)) {
                return true;
            }
        }

        return false;
    }

    private function refactorIf(If_ $if, string $privatePropertyName): ?bool
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($if->cond, function (Node $node) use (
            $privatePropertyName,
            &$isPropertyReadInIf
        ): ?int {
            if (! $this->propertyFetchAnalyzer->isLocalPropertyOfNames($node, [$privatePropertyName])) {
                return null;
            }

            $isPropertyReadInIf = true;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $isPropertyReadInIf;
    }

    private function isPropertyChanging(Node $node, string $privatePropertyName): bool
    {
        $isPropertyChanging = false;
        // here cannot be any property assign

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            &$isPropertyChanging,
            $privatePropertyName
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof PropertyFetch) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node->var->name, $privatePropertyName)) {
                return null;
            }

            $isPropertyChanging = true;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $isPropertyChanging;
    }
}
