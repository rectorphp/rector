<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\NodeScopeResolver as PHPStanNodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Broker\Broker;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\Stub\ClassReflectionForUnusedTrait;
use ReflectionClass;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

/**
 * @inspired by https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php
 * - https://github.com/silverstripe/silverstripe-upgrader/pull/57/commits/e5c7cfa166ad940d9d4ff69537d9f7608e992359#diff-5e0807bb3dc03d6a8d8b6ad049abd774
 */
final class NodeScopeResolver
{
    /**
     * @var PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;

    /**
     * @var ScopeFactory
     */
    private $scopeFactory;

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var RemoveDeepChainMethodCallNodeVisitor
     */
    private $removeDeepChainMethodCallNodeVisitor;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    public function __construct(
        ScopeFactory $scopeFactory,
        PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        Broker $broker,
        RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor,
        PrivatesAccessor $privatesAccessor
    ) {
        $this->scopeFactory = $scopeFactory;
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->broker = $broker;
        $this->removeDeepChainMethodCallNodeVisitor = $removeDeepChainMethodCallNodeVisitor;
        $this->privatesAccessor = $privatesAccessor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function processNodes(array $nodes, string $filePath): array
    {
        $this->removeDeepChainMethodCallNodes($nodes);

        $this->phpStanNodeScopeResolver->setAnalysedFiles([$filePath]);

        // skip chain method calls, performance issue: https://github.com/phpstan/phpstan/issues/254
        $this->phpStanNodeScopeResolver->processNodes(
            $nodes,
            $this->scopeFactory->createFromFile($filePath),
            function (Node $node, Scope $scope): void {
                // the class reflection is resolved AFTER entering to class node
                // so we need to get it from the first after this one
                if ($node instanceof Class_ || $node instanceof Interface_) {
                    $scope = $this->resolveClassOrInterfaceScope($node, $scope);
                } elseif ($node instanceof Trait_) {
                    $scope = $this->resolveTraitScope($node, $scope);
                }

                $node->setAttribute(AttributeKey::SCOPE, $scope);
            }
        );

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     */
    private function removeDeepChainMethodCallNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->removeDeepChainMethodCallNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Class_|Interface_ $classOrInterfaceNode
     */
    private function resolveClassOrInterfaceScope(Node $classOrInterfaceNode, Scope $scope): Scope
    {
        $className = $this->resolveClassName($classOrInterfaceNode);
        $classReflection = $this->broker->getClass($className);

        return $scope->enterClass($classReflection);
    }

    /**
     * @param Class_|Interface_|Trait_ $classOrInterfaceNode
     */
    private function resolveClassName(ClassLike $classOrInterfaceNode): string
    {
        if (isset($classOrInterfaceNode->namespacedName)) {
            return (string) $classOrInterfaceNode->namespacedName;
        }

        if ($classOrInterfaceNode->name === null) {
            throw new ShouldNotHappenException();
        }

        return $classOrInterfaceNode->name->toString();
    }

    private function resolveTraitScope(Trait_ $trait, Scope $scope): Scope
    {
        $traitName = $this->resolveClassName($trait);
        $traitReflection = $this->broker->getClass($traitName);

        /** @var ScopeContext $scopeContext */
        $scopeContext = $this->privatesAccessor->getPrivateProperty($scope, 'context');
        if ($scopeContext->getClassReflection() !== null) {
            return $scope->enterTrait($traitReflection);
        }

        // we need to emulate class reflection, because PHPStan is unable to analyze trait without it
        $classReflection = new ReflectionClass(ClassReflectionForUnusedTrait::class);
        $phpstanClassReflection = $this->broker->getClassFromReflection(
            $classReflection,
            ClassReflectionForUnusedTrait::class,
            null
        );

        // set stub
        $this->privatesAccessor->setPrivateProperty($scopeContext, 'classReflection', $phpstanClassReflection);

        $traitScope = $scope->enterTrait($traitReflection);
        $this->privatesAccessor->setPrivateProperty($scopeContext, 'classReflection', null);

        return $traitScope;
    }
}
