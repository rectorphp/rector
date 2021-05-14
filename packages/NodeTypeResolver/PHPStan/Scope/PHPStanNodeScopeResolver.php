<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\FileSystem\DependencyResolver;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
use RectorPrefix20210514\Symfony\Component\Console\Style\SymfonyStyle;
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
     * @var string[]
     */
    private $dependentFiles = [];
    /**
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var \Rector\Core\Configuration\Configuration
     */
    private $configuration;
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
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;
    public function __construct(\Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Core\Configuration\Configuration $configuration, \Rector\Caching\FileSystem\DependencyResolver $dependencyResolver, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory $scopeFactory, \RectorPrefix20210514\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector $traitNodeScopeCollector)
    {
        $this->changedFilesDetector = $changedFilesDetector;
        $this->configuration = $configuration;
        $this->dependencyResolver = $dependencyResolver;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->removeDeepChainMethodCallNodeVisitor = $removeDeepChainMethodCallNodeVisitor;
        $this->scopeFactory = $scopeFactory;
        $this->symfonyStyle = $symfonyStyle;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
    }
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function processNodes(array $nodes, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $this->removeDeepChainMethodCallNodes($nodes);
        $scope = $this->scopeFactory->createFromFile($smartFileInfo);
        $this->dependentFiles = [];
        // skip chain method calls, performance issue: https://github.com/phpstan/phpstan/issues/254
        $nodeCallback = function (\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : void {
            // traversing trait inside class that is using it scope (from referenced) - the trait traversed by Rector is different (directly from parsed file)
            if ($scope->isInTrait()) {
                /** @var ClassReflection $classReflection */
                $classReflection = $scope->getTraitReflection();
                $traitName = $classReflection->getName();
                $this->traitNodeScopeCollector->addForTraitAndNode($traitName, $node, $scope);
                return;
            }
            // the class reflection is resolved AFTER entering to class node
            // so we need to get it from the first after this one
            if ($node instanceof \PhpParser\Node\Stmt\Class_ || $node instanceof \PhpParser\Node\Stmt\Interface_) {
                /** @var Scope $scope */
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
        foreach ($nodes as $node) {
            $this->resolveDependentFiles($node, $scope);
        }
        /** @var MutatingScope $scope */
        $this->nodeScopeResolver->processNodes($nodes, $scope, $nodeCallback);
        $this->reportCacheDebugAndSaveDependentFiles($smartFileInfo, $this->dependentFiles);
        return $nodes;
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
     * @param Class_|Interface_ $classLike
     */
    private function resolveClassOrInterfaceScope(\PhpParser\Node\Stmt\ClassLike $classLike, \PHPStan\Analyser\Scope $scope) : \PHPStan\Analyser\Scope
    {
        $className = $this->resolveClassName($classLike);
        // is anonymous class? - not possible to enter it since PHPStan 0.12.33, see https://github.com/phpstan/phpstan-src/commit/e87fb0ec26f9c8552bbeef26a868b1e5d8185e91
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_ && \RectorPrefix20210514\Nette\Utils\Strings::match($className, self::ANONYMOUS_CLASS_START_REGEX)) {
            $classReflection = $this->reflectionProvider->getAnonymousClassReflection($classLike, $scope);
        } elseif (!$this->reflectionProvider->hasClass($className)) {
            return $scope;
        } else {
            $classReflection = $this->reflectionProvider->getClass($className);
        }
        /** @var MutatingScope $scope */
        return $scope->enterClass($classReflection);
    }
    private function resolveDependentFiles(\PhpParser\Node $node, \PHPStan\Analyser\MutatingScope $mutatingScope) : void
    {
        if (!$this->configuration->isCacheEnabled()) {
            return;
        }
        try {
            $dependentFiles = $this->dependencyResolver->resolveDependencies($node, $mutatingScope);
            foreach ($dependentFiles as $dependentFile) {
                $this->dependentFiles[] = $dependentFile;
            }
        } catch (\PHPStan\AnalysedCodeException $analysedCodeException) {
            // @ignoreException
        }
    }
    /**
     * @param string[] $dependentFiles
     */
    private function reportCacheDebugAndSaveDependentFiles(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $dependentFiles) : void
    {
        if (!$this->configuration->isCacheEnabled()) {
            return;
        }
        $this->reportCacheDebug($smartFileInfo, $dependentFiles);
        // save for cache
        $this->changedFilesDetector->addFileWithDependencies($smartFileInfo, $dependentFiles);
    }
    /**
     * @param Class_|Interface_|Trait_ $classLike
     */
    private function resolveClassName(\PhpParser\Node\Stmt\ClassLike $classLike) : string
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
     * @param string[] $dependentFiles
     */
    private function reportCacheDebug(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $dependentFiles) : void
    {
        if (!$this->configuration->isCacheDebug()) {
            return;
        }
        $message = \sprintf('[debug] %d dependencies for "%s" file', \count($dependentFiles), $smartFileInfo->getRealPath());
        $this->symfonyStyle->note($message);
        if ($dependentFiles !== []) {
            $this->symfonyStyle->listing($dependentFiles);
        }
    }
}
