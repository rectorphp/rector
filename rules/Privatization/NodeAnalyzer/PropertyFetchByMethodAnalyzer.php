<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
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
use RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class PropertyFetchByMethodAnalyzer
{
    /**
     * @var array<class-string<Stmt>>
     */
    private const SCOPE_CHANGING_NODE_TYPES = [\PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Else_::class];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @param string[] $propertyNames
     * @return array<string, string[]>
     */
    public function collectPropertyFetchByMethods(\PhpParser\Node\Stmt\Class_ $class, array $propertyNames) : array
    {
        $propertyUsageByMethods = [];
        foreach ($propertyNames as $propertyName) {
            if ($this->isPropertyWithDefaultValue($class, $propertyName)) {
                continue;
            }
            foreach ($class->getMethods() as $classMethod) {
                // assigned in constructor injection → skip
                if ($this->isInConstructWithPropertyChanging($classMethod, $propertyName)) {
                    return [];
                }
                if ($this->isContainsLocalPropertyFetchNameOrPropertyChangingInMultipleMethodCalls($classMethod, $propertyName)) {
                    continue;
                }
                if (!$this->isPropertyChanging($classMethod, $propertyName)) {
                    continue;
                }
                $classMethodName = $this->nodeNameResolver->getName($classMethod);
                $propertyUsageByMethods[$propertyName][] = $classMethodName;
            }
        }
        return $propertyUsageByMethods;
    }
    private function isContainsLocalPropertyFetchNameOrPropertyChangingInMultipleMethodCalls(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : bool
    {
        if (!$this->propertyFetchAnalyzer->containsLocalPropertyFetchName($classMethod, $propertyName)) {
            return \true;
        }
        return $this->isPropertyChangingInMultipleMethodCalls($classMethod, $propertyName);
    }
    private function isPropertyWithDefaultValue(\PhpParser\Node\Stmt\Class_ $class, string $propertyName) : bool
    {
        $property = $class->getProperty($propertyName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return \false;
        }
        return $property->props[0]->default instanceof \PhpParser\Node\Expr;
    }
    private function isInConstructWithPropertyChanging(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : bool
    {
        if (!$this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return \false;
        }
        return $this->isPropertyChanging($classMethod, $propertyName);
    }
    /**
     * Covers https://github.com/rectorphp/rector/pull/2558#discussion_r363036110
     */
    private function isPropertyChangingInMultipleMethodCalls(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : bool
    {
        $isPropertyChanging = \false;
        $isPropertyReadInIf = \false;
        $isIfFollowedByAssign = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (\PhpParser\Node $node) use(&$isPropertyChanging, $propertyName, &$isPropertyReadInIf, &$isIfFollowedByAssign) : ?int {
            if ($isPropertyReadInIf) {
                if (!$this->propertyFetchAnalyzer->isLocalPropertyOfNames($node, [$propertyName])) {
                    return null;
                }
                $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                if ($parentNode instanceof \PhpParser\Node\Expr\Assign && $parentNode->var === $node) {
                    $isIfFollowedByAssign = \true;
                }
            }
            if (!$this->isScopeChangingNode($node)) {
                return null;
            }
            $isPropertyReadInIf = $this->isPropertyReadInNode($isPropertyReadInIf, $node, $propertyName);
            $isPropertyChanging = $this->isPropertyChanging($node, $propertyName);
            if (!$isPropertyChanging) {
                return null;
            }
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
        return $isPropertyChanging || $isIfFollowedByAssign || $isPropertyReadInIf;
    }
    private function isPropertyReadInNode(bool $isPropertyReadInIf, \PhpParser\Node $node, string $propertyName) : bool
    {
        if ($node instanceof \PhpParser\Node\Stmt\If_) {
            return $this->isPropertyReadInIf($node, $propertyName);
        }
        return $isPropertyReadInIf;
    }
    private function isScopeChangingNode(\PhpParser\Node $node) : bool
    {
        foreach (self::SCOPE_CHANGING_NODE_TYPES as $type) {
            if (\is_a($node, $type, \true)) {
                return \true;
            }
        }
        return \false;
    }
    private function isPropertyReadInIf(\PhpParser\Node\Stmt\If_ $if, string $propertyName) : bool
    {
        $isPropertyReadInIf = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($if->cond, function (\PhpParser\Node $node) use($propertyName, &$isPropertyReadInIf) : ?int {
            if (!$this->propertyFetchAnalyzer->isLocalPropertyOfNames($node, [$propertyName])) {
                return null;
            }
            $isPropertyReadInIf = \true;
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
        return $isPropertyReadInIf;
    }
    private function isPropertyChanging(\PhpParser\Node $node, string $privatePropertyName) : bool
    {
        $isPropertyChanging = \false;
        // here cannot be any property assign
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, function (\PhpParser\Node $node) use(&$isPropertyChanging, $privatePropertyName) : ?int {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var->name, $privatePropertyName)) {
                return null;
            }
            $isPropertyChanging = \true;
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
        return $isPropertyChanging;
    }
}
