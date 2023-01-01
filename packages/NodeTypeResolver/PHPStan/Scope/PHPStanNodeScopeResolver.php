<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeTraverser;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeContext;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Reflection\BetterReflection\Reflector\MemoizingReflector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\FileSystem\DependencyResolver;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\Core\Util\StringUtils;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
use RectorPrefix202301\Webmozart\Assert\Assert;
/**
 * @inspired by https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php
 * - https://github.com/silverstripe/silverstripe-upgrader/pull/57/commits/e5c7cfa166ad940d9d4ff69537d9f7608e992359#diff-5e0807bb3dc03d6a8d8b6ad049abd774
 */
final class PHPStanNodeScopeResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/aXsCkK/1
     */
    private const ANONYMOUS_CLASS_START_REGEX = '#^AnonymousClass(\\w+)#';
    /**
     * @var string
     */
    private const CONTEXT = 'context';
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\Caching\FileSystem\DependencyResolver
     */
    private $dependencyResolver;
    /**
     * @readonly
     * @var \PHPStan\Analyser\NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor
     */
    private $removeDeepChainMethodCallNodeVisitor;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory
     */
    private $scopeFactory;
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator
     */
    private $renamedClassesSourceLocator;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(ChangedFilesDetector $changedFilesDetector, DependencyResolver $dependencyResolver, NodeScopeResolver $nodeScopeResolver, ReflectionProvider $reflectionProvider, RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory $scopeFactory, PrivatesAccessor $privatesAccessor, RenamedClassesSourceLocator $renamedClassesSourceLocator, NodeNameResolver $nodeNameResolver)
    {
        $this->changedFilesDetector = $changedFilesDetector;
        $this->dependencyResolver = $dependencyResolver;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->removeDeepChainMethodCallNodeVisitor = $removeDeepChainMethodCallNodeVisitor;
        $this->scopeFactory = $scopeFactory;
        $this->privatesAccessor = $privatesAccessor;
        $this->renamedClassesSourceLocator = $renamedClassesSourceLocator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->decoratePHPStanNodeScopeResolverWithRenamedClassSourceLocator($this->nodeScopeResolver);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function processNodes(array $stmts, string $filePath, ?MutatingScope $formerMutatingScope = null) : array
    {
        $isScopeRefreshing = $formerMutatingScope instanceof MutatingScope;
        /**
         * The stmts must be array of Stmt, or it will be silently skipped by PHPStan
         * @see vendor/phpstan/phpstan/phpstan.phar/src/Analyser/NodeScopeResolver.php:282
         */
        Assert::allIsInstanceOf($stmts, Stmt::class);
        $this->removeDeepChainMethodCallNodes($stmts);
        $scope = $formerMutatingScope ?? $this->scopeFactory->createFromFile($filePath);
        // skip chain method calls, performance issue: https://github.com/phpstan/phpstan/issues/254
        $nodeCallback = function (Node $node, MutatingScope $mutatingScope) use(&$nodeCallback, $isScopeRefreshing, $filePath) : void {
            if (($node instanceof Expression || $node instanceof Return_ || $node instanceof Assign || $node instanceof EnumCase || $node instanceof AssignOp) && $node->expr instanceof Expr) {
                $node->expr->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
            if ($node instanceof Ternary) {
                $this->processTernary($node, $mutatingScope);
            }
            if ($node instanceof BinaryOp) {
                $this->processBinaryOp($node, $mutatingScope);
            }
            if ($node instanceof Arg) {
                $node->value->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
            if ($node instanceof Foreach_) {
                // decorate value as well
                $node->valueVar->setAttribute(AttributeKey::SCOPE, $mutatingScope);
                if ($node->valueVar instanceof Array_) {
                    $this->processArray($node->valueVar, $mutatingScope);
                }
            }
            if ($node instanceof Array_) {
                $this->processArray($node, $mutatingScope);
            }
            if ($node instanceof Property) {
                $this->processProperty($node, $mutatingScope);
            }
            if ($node instanceof Switch_) {
                $this->processSwitch($node, $mutatingScope);
            }
            if ($node instanceof TryCatch) {
                $this->processTryCatch($node, $filePath, $mutatingScope);
            }
            if ($node instanceof ArrayItem) {
                $this->processArrayItem($node, $mutatingScope);
            }
            if ($node instanceof FuncCall && $node->name instanceof Expr) {
                $node->name->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
            if ($node instanceof Assign) {
                // decorate value as well
                $node->var->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
            if ($node instanceof Trait_) {
                $traitName = $this->resolveClassName($node);
                $traitReflectionClass = $this->reflectionProvider->getClass($traitName);
                $traitScope = clone $mutatingScope;
                $scopeContext = $this->privatesAccessor->getPrivatePropertyOfClass($traitScope, self::CONTEXT, ScopeContext::class);
                $traitContext = clone $scopeContext;
                // before entering the class/trait again, we have to tell scope no class was set, otherwise it crashes
                $this->privatesAccessor->setPrivatePropertyOfClass($traitContext, 'classReflection', $traitReflectionClass, ClassReflection::class);
                $this->privatesAccessor->setPrivatePropertyOfClass($traitScope, self::CONTEXT, $traitContext, ScopeContext::class);
                $node->setAttribute(AttributeKey::SCOPE, $traitScope);
                $this->nodeScopeResolver->processNodes($node->stmts, $traitScope, $nodeCallback);
                $this->decorateTraitAttrGroups($node, $traitScope);
                return;
            }
            // the class reflection is resolved AFTER entering to class node
            // so we need to get it from the first after this one
            if ($node instanceof Class_ || $node instanceof Interface_ || $node instanceof Enum_) {
                /** @var MutatingScope $mutatingScope */
                $mutatingScope = $this->resolveClassOrInterfaceScope($node, $mutatingScope, $isScopeRefreshing);
            }
            // special case for unreachable nodes
            if ($node instanceof UnreachableStatementNode) {
                $this->processUnreachableStatementNode($node, $filePath, $mutatingScope);
            } else {
                $node->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
        };
        return $this->processNodesWithDependentFiles($filePath, $stmts, $scope, $nodeCallback);
    }
    private function processArray(Array_ $array, MutatingScope $mutatingScope) : void
    {
        foreach ($array->items as $arrayItem) {
            if ($arrayItem instanceof ArrayItem) {
                $arrayItem->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
        }
    }
    private function processArrayItem(ArrayItem $arrayItem, MutatingScope $mutatingScope) : void
    {
        if ($arrayItem->key instanceof Expr) {
            $arrayItem->key->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        }
        $arrayItem->value->setAttribute(AttributeKey::SCOPE, $mutatingScope);
    }
    private function decorateTraitAttrGroups(Trait_ $trait, MutatingScope $mutatingScope) : void
    {
        foreach ($trait->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                foreach ($attr->args as $arg) {
                    $arg->value->setAttribute(AttributeKey::SCOPE, $mutatingScope);
                }
            }
        }
    }
    private function processSwitch(Switch_ $switch, MutatingScope $mutatingScope) : void
    {
        // decorate value as well
        foreach ($switch->cases as $case) {
            $case->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        }
    }
    private function processTryCatch(TryCatch $tryCatch, string $filePath, MutatingScope $mutatingScope) : void
    {
        foreach ($tryCatch->catches as $catch) {
            $varName = $catch->var instanceof Variable ? $this->nodeNameResolver->getName($catch->var) : null;
            $type = TypeCombinator::union(...\array_map(static function (Name $name) : ObjectType {
                return new ObjectType((string) $name);
            }, $catch->types));
            $catchMutatingScope = $mutatingScope->enterCatchType($type, $varName);
            $this->processNodes($catch->stmts, $filePath, $catchMutatingScope);
        }
        if ($tryCatch->finally instanceof Finally_) {
            $tryCatch->finally->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        }
    }
    private function processUnreachableStatementNode(UnreachableStatementNode $unreachableStatementNode, string $filePath, MutatingScope $mutatingScope) : void
    {
        $originalStmt = $unreachableStatementNode->getOriginalStatement();
        $originalStmt->setAttribute(AttributeKey::IS_UNREACHABLE, \true);
        $originalStmt->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        $this->processNodes([$originalStmt], $filePath, $mutatingScope);
        $nextNode = $originalStmt->getAttribute(AttributeKey::NEXT_NODE);
        while ($nextNode instanceof Stmt) {
            $nextNode->setAttribute(AttributeKey::IS_UNREACHABLE, \true);
            $this->processNodes([$nextNode], $filePath, $mutatingScope);
            $nextNode = $nextNode->getAttribute(AttributeKey::NEXT_NODE);
        }
    }
    private function processProperty(Property $property, MutatingScope $mutatingScope) : void
    {
        foreach ($property->props as $propertyProperty) {
            $propertyProperty->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            if ($propertyProperty->default instanceof Expr) {
                $propertyProperty->default->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
        }
        foreach ($property->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $attribute->setAttribute(AttributeKey::SCOPE, $mutatingScope);
            }
        }
    }
    private function processBinaryOp(BinaryOp $binaryOp, MutatingScope $mutatingScope) : void
    {
        $binaryOp->left->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        $binaryOp->right->setAttribute(AttributeKey::SCOPE, $mutatingScope);
    }
    private function processTernary(Ternary $ternary, MutatingScope $mutatingScope) : void
    {
        if ($ternary->if instanceof Expr) {
            $ternary->if->setAttribute(AttributeKey::SCOPE, $mutatingScope);
        }
        $ternary->else->setAttribute(AttributeKey::SCOPE, $mutatingScope);
    }
    /**
     * @param Stmt[] $stmts
     * @param callable(Node $node, MutatingScope $scope): void $nodeCallback
     * @return Stmt[]
     */
    private function processNodesWithDependentFiles(string $filePath, array $stmts, MutatingScope $mutatingScope, callable $nodeCallback) : array
    {
        $this->nodeScopeResolver->processNodes($stmts, $mutatingScope, $nodeCallback);
        $this->resolveAndSaveDependentFiles($stmts, $mutatingScope, $filePath);
        return $stmts;
    }
    /**
     * @param Node[] $nodes
     */
    private function removeDeepChainMethodCallNodes(array $nodes) : void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->removeDeepChainMethodCallNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Enum_ $classLike
     */
    private function resolveClassOrInterfaceScope($classLike, MutatingScope $mutatingScope, bool $isScopeRefreshing) : MutatingScope
    {
        $className = $this->resolveClassName($classLike);
        // is anonymous class? - not possible to enter it since PHPStan 0.12.33, see https://github.com/phpstan/phpstan-src/commit/e87fb0ec26f9c8552bbeef26a868b1e5d8185e91
        if ($classLike instanceof Class_ && StringUtils::isMatch($className, self::ANONYMOUS_CLASS_START_REGEX)) {
            $classReflection = $this->reflectionProvider->getAnonymousClassReflection($classLike, $mutatingScope);
        } elseif (!$this->reflectionProvider->hasClass($className)) {
            return $mutatingScope;
        } else {
            $classReflection = $this->reflectionProvider->getClass($className);
        }
        // on refresh, remove entered class avoid entering the class again
        if ($isScopeRefreshing && $mutatingScope->isInClass() && !$classReflection->isAnonymous()) {
            $context = $this->privatesAccessor->getPrivateProperty($mutatingScope, 'context');
            $this->privatesAccessor->setPrivateProperty($context, 'classReflection', null);
        }
        return $mutatingScope->enterClass($classReflection);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Enum_ $classLike
     */
    private function resolveClassName($classLike) : string
    {
        if ($classLike->namespacedName instanceof Name) {
            return (string) $classLike->namespacedName;
        }
        if ($classLike->name === null) {
            throw new ShouldNotHappenException();
        }
        return $classLike->name->toString();
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveAndSaveDependentFiles(array $stmts, MutatingScope $mutatingScope, string $filePath) : void
    {
        $dependentFiles = [];
        foreach ($stmts as $stmt) {
            try {
                $nodeDependentFiles = $this->dependencyResolver->resolveDependencies($stmt, $mutatingScope);
                $dependentFiles = \array_merge($dependentFiles, $nodeDependentFiles);
            } catch (AnalysedCodeException $exception) {
                // @ignoreException
            }
        }
        $this->changedFilesDetector->addFileWithDependencies($filePath, $dependentFiles);
    }
    /**
     * In case PHPStan tried to parse a file with missing class, it fails.
     * But sometimes we want to rename old class that is missing with Rector..
     *
     * That's why we have to skip fatal errors of PHPStan caused by missing class,
     * so Rector can fix it first. Then run Rector again to refactor code with new classes.
     */
    private function decoratePHPStanNodeScopeResolverWithRenamedClassSourceLocator(NodeScopeResolver $nodeScopeResolver) : void
    {
        // 1. get PHPStan locator
        /** @var MemoizingReflector $classReflector */
        $classReflector = $this->privatesAccessor->getPrivatePropertyOfClass($nodeScopeResolver, 'reflector', Reflector::class);
        $reflector = $this->privatesAccessor->getPrivatePropertyOfClass($classReflector, 'reflector', Reflector::class);
        /** @var SourceLocator $sourceLocator */
        $sourceLocator = $this->privatesAccessor->getPrivatePropertyOfClass($reflector, 'sourceLocator', SourceLocator::class);
        // 2. get Rector locator
        $aggregateSourceLocator = new AggregateSourceLocator([$sourceLocator, $this->renamedClassesSourceLocator]);
        $this->privatesAccessor->setPrivatePropertyOfClass($reflector, 'sourceLocator', $aggregateSourceLocator, AggregateSourceLocator::class);
    }
}
