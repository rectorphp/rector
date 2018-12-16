<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\NodeScopeResolver as PHPStanNodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
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

    public function __construct(
        ScopeFactory $scopeFactory,
        PHPStanNodeScopeResolver $phpStanNodeScopeResolver,
        Broker $broker,
        RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor
    ) {
        $this->scopeFactory = $scopeFactory;
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->broker = $broker;
        $this->removeDeepChainMethodCallNodeVisitor = $removeDeepChainMethodCallNodeVisitor;
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
                    $scope = $this->resolveClassOrInterfaceNode($node, $scope);
                }

                $node->setAttribute(Attribute::SCOPE, $scope);
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
    private function resolveClassOrInterfaceNode(Node $classOrInterfaceNode, Scope $scope): Scope
    {
        if (isset($classOrInterfaceNode->namespacedName)) {
            return $scope->enterClass($this->broker->getClass((string) $classOrInterfaceNode->namespacedName));
        }

        // possibly anonymous class
        $anonymousClassReflection = (new PrivatesAccessor())->getPrivateProperty(
            $this->phpStanNodeScopeResolver,
            'anonymousClassReflection'
        );

        if ($anonymousClassReflection) {
            return $scope->enterAnonymousClass($anonymousClassReflection);
        }

        return $scope;
    }
}
