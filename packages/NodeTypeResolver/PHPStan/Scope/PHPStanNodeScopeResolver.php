<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\BetterReflection\Reflection\Exception\NotAnInterfaceReflection;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\FileSystem\DependencyResolver;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\StaticReflection\SourceLocator\ParentAttributeSourceLocator;
use Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator;
use Rector\Core\Stubs\DummyTraitClass;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;
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
     * @see https://regex101.com/r/AIA24M/1
     */
    private const NOT_AN_INTERFACE_EXCEPTION_REGEX = '#^Provided node ".*" is not interface, but "class"$#';
    /**
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var \Rector\Caching\FileSystem\DependencyResolver
     */
    private $dependencyResolver;
    /**
     * @var \PHPStan\Analyser\NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor
     */
    private $removeDeepChainMethodCallNodeVisitor;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory
     */
    private $scopeFactory;
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @var \Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator
     */
    private $renamedClassesSourceLocator;
    /**
     * @var \Rector\Core\StaticReflection\SourceLocator\ParentAttributeSourceLocator
     */
    private $parentAttributeSourceLocator;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Caching\FileSystem\DependencyResolver $dependencyResolver, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory $scopeFactory, \RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\Core\StaticReflection\SourceLocator\RenamedClassesSourceLocator $renamedClassesSourceLocator, \Rector\Core\StaticReflection\SourceLocator\ParentAttributeSourceLocator $parentAttributeSourceLocator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
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
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function processNodes(array $nodes, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $this->removeDeepChainMethodCallNodes($nodes);
        $scope = $this->scopeFactory->createFromFile($smartFileInfo);
        // skip chain method calls, performance issue: https://github.com/phpstan/phpstan/issues/254
        $nodeCallback = function (\PhpParser\Node $node, \PHPStan\Analyser\MutatingScope $scope) use(&$nodeCallback) : void {
            if ($node instanceof \PhpParser\Node\Stmt\Trait_) {
                $traitName = $this->resolveClassName($node);
                $traitReflectionClass = $this->reflectionProvider->getClass($traitName);
                $scopeContext = $this->createDummyClassScopeContext($scope);
                $traitScope = clone $scope;
                $this->privatesAccessor->setPrivateProperty($traitScope, 'context', $scopeContext);
                $traitScope = $traitScope->enterTrait($traitReflectionClass);
                $this->nodeScopeResolver->processStmtNodes($node, $node->stmts, $traitScope, $nodeCallback);
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
        return $this->processNodesWithMixinHandling($smartFileInfo, $nodes, $scope, $nodeCallback);
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    private function processNodesWithMixinHandling(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $nodes, \PHPStan\Analyser\MutatingScope $mutatingScope, callable $nodeCallback) : array
    {
        if ($this->isMixinInSource($nodes)) {
            return $nodes;
        }
        try {
            $this->nodeScopeResolver->processNodes($nodes, $mutatingScope, $nodeCallback);
        } catch (\Throwable $throwable) {
            if (!$throwable instanceof \PHPStan\BetterReflection\Reflection\Exception\NotAnInterfaceReflection) {
                throw $throwable;
            }
            if (!\RectorPrefix20211020\Nette\Utils\Strings::match($throwable->getMessage(), self::NOT_AN_INTERFACE_EXCEPTION_REGEX)) {
                throw $throwable;
            }
        }
        $this->resolveAndSaveDependentFiles($nodes, $mutatingScope, $smartFileInfo);
        return $nodes;
    }
    /**
     * @param Node[] $nodes
     */
    private function isMixinInSource(array $nodes) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Name\FullyQualified && !$node instanceof \PhpParser\Node\Stmt\Class_) {
                return \false;
            }
            if ($node instanceof \PhpParser\Node\Stmt\Class_ && $node->isAnonymous()) {
                return \false;
            }
            $className = $node instanceof \PhpParser\Node\Name\FullyQualified ? $node->toString() : $node->namespacedName->toString();
            return $this->isCircularMixin($className);
        });
    }
    private function isCircularMixin(string $className) : bool
    {
        // fix error in parallel test
        // use function_exists on purpose as using reflectionProvider broke the test in parallel
        if (\function_exists($className)) {
            return \false;
        }
        $hasClass = $this->reflectionProvider->hasClass($className);
        if (!$hasClass) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->isBuiltIn()) {
            return \false;
        }
        foreach ($classReflection->getMixinTags() as $mixinTag) {
            $type = $mixinTag->getType();
            if (!$type instanceof \PHPStan\Type\ObjectType) {
                return \false;
            }
            if ($type->getClassName() === $className) {
                return \true;
            }
            if ($this->isCircularMixin($type->getClassName())) {
                return \true;
            }
        }
        return \false;
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
    private function resolveClassOrInterfaceScope($classLike, \PHPStan\Analyser\MutatingScope $mutatingScope) : \PHPStan\Analyser\Scope
    {
        $className = $this->resolveClassName($classLike);
        // is anonymous class? - not possible to enter it since PHPStan 0.12.33, see https://github.com/phpstan/phpstan-src/commit/e87fb0ec26f9c8552bbeef26a868b1e5d8185e91
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_ && \RectorPrefix20211020\Nette\Utils\Strings::match($className, self::ANONYMOUS_CLASS_START_REGEX)) {
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
        /** @var ClassReflector $classReflector */
        $classReflector = $this->privatesAccessor->getPrivateProperty($nodeScopeResolver, 'classReflector');
        /** @var SourceLocator $sourceLocator */
        $sourceLocator = $this->privatesAccessor->getPrivateProperty($classReflector, 'sourceLocator');
        // 2. get Rector locator
        $aggregateSourceLocator = new \PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator([$sourceLocator, $this->renamedClassesSourceLocator, $this->parentAttributeSourceLocator]);
        $this->privatesAccessor->setPrivateProperty($classReflector, 'sourceLocator', $aggregateSourceLocator);
    }
    private function createDummyClassScopeContext(\PHPStan\Analyser\MutatingScope $mutatingScope) : \PHPStan\Analyser\ScopeContext
    {
        // this has to be faked, because trait PHPStan does not traverse trait without a class
        /** @var ScopeContext $scopeContext */
        $scopeContext = $this->privatesAccessor->getPrivateProperty($mutatingScope, 'context');
        $dummyClassReflection = $this->reflectionProvider->getClass(\Rector\Core\Stubs\DummyTraitClass::class);
        // faking a class reflection
        return \PHPStan\Analyser\ScopeContext::create($scopeContext->getFile())->enterClass($dummyClassReflection);
    }
}
