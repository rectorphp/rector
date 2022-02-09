<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
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
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\FileSystem\DependencyResolver;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\StaticReflection\SourceLocator\ParentAttributeSourceLocator;
use Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
use RectorPrefix20220209\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SmartFileSystem\SmartFileInfo;
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
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator
     */
    private $renamedClassesSourceLocator;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\SourceLocator\ParentAttributeSourceLocator
     */
    private $parentAttributeSourceLocator;
    public function __construct(\Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Caching\FileSystem\DependencyResolver $dependencyResolver, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory $scopeFactory, \RectorPrefix20220209\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator $renamedClassesSourceLocator, \Rector\Core\StaticReflection\SourceLocator\ParentAttributeSourceLocator $parentAttributeSourceLocator)
    {
        $this->changedFilesDetector = $changedFilesDetector;
        $this->dependencyResolver = $dependencyResolver;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->removeDeepChainMethodCallNodeVisitor = $removeDeepChainMethodCallNodeVisitor;
        $this->scopeFactory = $scopeFactory;
        $this->privatesAccessor = $privatesAccessor;
        $this->renamedClassesSourceLocator = $renamedClassesSourceLocator;
        $this->parentAttributeSourceLocator = $parentAttributeSourceLocator;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function processNodes(array $stmts, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $this->removeDeepChainMethodCallNodes($stmts);
        $scope = $this->scopeFactory->createFromFile($smartFileInfo);
        // skip chain method calls, performance issue: https://github.com/phpstan/phpstan/issues/254
        $nodeCallback = function (\PhpParser\Node $node, \PHPStan\Analyser\MutatingScope $scope) use(&$nodeCallback) : void {
            if ($node instanceof \PhpParser\Node\Stmt\Trait_) {
                $traitName = $this->resolveClassName($node);
                $traitReflectionClass = $this->reflectionProvider->getClass($traitName);
                $traitScope = clone $scope;
                $scopeContext = $this->privatesAccessor->getPrivatePropertyOfClass($traitScope, self::CONTEXT, \PHPStan\Analyser\ScopeContext::class);
                $traitContext = clone $scopeContext;
                $this->privatesAccessor->setPrivatePropertyOfClass($traitContext, 'classReflection', $traitReflectionClass, \PHPStan\Reflection\ClassReflection::class);
                $this->privatesAccessor->setPrivatePropertyOfClass($traitScope, self::CONTEXT, $traitContext, \PHPStan\Analyser\ScopeContext::class);
                $this->nodeScopeResolver->processNodes($node->stmts, $traitScope, $nodeCallback);
                return;
            }
            // the class reflection is resolved AFTER entering to class node
            // so we need to get it from the first after this one
            if ($node instanceof \PhpParser\Node\Stmt\Class_ || $node instanceof \PhpParser\Node\Stmt\Interface_) {
                /** @var MutatingScope $scope */
                $scope = $this->resolveClassOrInterfaceScope($node, $scope);
            }
            // special case for unreachable nodes
            if ($node instanceof \PHPStan\Node\UnreachableStatementNode) {
                $originalNode = $node->getOriginalStatement();
                $originalNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE, \true);
                $originalNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, $scope);
            } else {
                $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE, $scope);
            }
        };
        $this->decoratePHPStanNodeScopeResolverWithRenamedClassSourceLocator($this->nodeScopeResolver);
        return $this->processNodesWithDependentFiles($smartFileInfo, $stmts, $scope, $nodeCallback);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function processNodesWithDependentFiles(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $stmts, \PHPStan\Analyser\MutatingScope $mutatingScope, callable $nodeCallback) : array
    {
        $this->nodeScopeResolver->processNodes($stmts, $mutatingScope, $nodeCallback);
        $this->resolveAndSaveDependentFiles($stmts, $mutatingScope, $smartFileInfo);
        return $stmts;
    }
    /**
     * @param Node[] $nodes
     */
    private function removeDeepChainMethodCallNodes(array $nodes) : void
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($this->removeDeepChainMethodCallNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_ $classLike
     */
    private function resolveClassOrInterfaceScope($classLike, \PHPStan\Analyser\MutatingScope $mutatingScope) : \PHPStan\Analyser\MutatingScope
    {
        $className = $this->resolveClassName($classLike);
        // is anonymous class? - not possible to enter it since PHPStan 0.12.33, see https://github.com/phpstan/phpstan-src/commit/e87fb0ec26f9c8552bbeef26a868b1e5d8185e91
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_ && \Rector\Core\Util\StringUtils::isMatch($className, self::ANONYMOUS_CLASS_START_REGEX)) {
            $classReflection = $this->reflectionProvider->getAnonymousClassReflection($classLike, $mutatingScope);
        } elseif (!$this->reflectionProvider->hasClass($className)) {
            return $mutatingScope;
        } else {
            $classReflection = $this->reflectionProvider->getClass($className);
        }
        return $mutatingScope->enterClass($classReflection);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Trait_ $classLike
     */
    private function resolveClassName($classLike) : string
    {
        if (\property_exists($classLike, 'namespacedName')) {
            return (string) $classLike->namespacedName;
        }
        if ($classLike->name === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $classLike->name->toString();
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveAndSaveDependentFiles(array $stmts, \PHPStan\Analyser\MutatingScope $mutatingScope, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : void
    {
        $dependentFiles = [];
        foreach ($stmts as $stmt) {
            try {
                $nodeDependentFiles = $this->dependencyResolver->resolveDependencies($stmt, $mutatingScope);
                $dependentFiles = \array_merge($dependentFiles, $nodeDependentFiles);
            } catch (\PHPStan\AnalysedCodeException $exception) {
                // @ignoreException
            }
        }
        $this->changedFilesDetector->addFileWithDependencies($smartFileInfo, $dependentFiles);
    }
    /**
     * In case PHPStan tried to parse a file with missing class, it fails.
     * But sometimes we want to rename old class that is missing with Rector..
     *
     * That's why we have to skip fatal errors of PHPStan caused by missing class,
     * so Rector can fix it first. Then run Rector again to refactor code with new classes.
     */
    private function decoratePHPStanNodeScopeResolverWithRenamedClassSourceLocator(\PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver) : void
    {
        // 1. get PHPStan locator
        /** @var MemoizingReflector $classReflector */
        $classReflector = $this->privatesAccessor->getPrivatePropertyOfClass($nodeScopeResolver, 'reflector', \PHPStan\BetterReflection\Reflector\Reflector::class);
        $reflector = $this->privatesAccessor->getPrivatePropertyOfClass($classReflector, 'reflector', \PHPStan\BetterReflection\Reflector\Reflector::class);
        /** @var SourceLocator $sourceLocator */
        $sourceLocator = $this->privatesAccessor->getPrivatePropertyOfClass($reflector, 'sourceLocator', \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator::class);
        // 2. get Rector locator
        $aggregateSourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator([$sourceLocator, $this->renamedClassesSourceLocator, $this->parentAttributeSourceLocator]);
        $this->privatesAccessor->setPrivatePropertyOfClass($reflector, 'sourceLocator', $aggregateSourceLocator, \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator::class);
    }
}
