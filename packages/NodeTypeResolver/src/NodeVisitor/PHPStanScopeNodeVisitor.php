<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use Nette\DI\Container;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\ContainerFactory;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Configuration\CurrentFileProvider;

/**
 * @inspired by https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php
 * - https://github.com/silverstripe/silverstripe-upgrader/pull/57/commits/e5c7cfa166ad940d9d4ff69537d9f7608e992359#diff-5e0807bb3dc03d6a8d8b6ad049abd774
 */
final class PHPStanScopeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        // Setup application for this parse
        $containerFactory = new ContainerFactory(getcwd());
        $this->container = $containerFactory->create(sys_get_temp_dir(), []);
        $this->nodeScopeResolver = $this->container->getByType(NodeScopeResolver::class);
        $this->currentFileProvider = $currentFileProvider;
    }

    /**
     * @param Stmt[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        /** @var Broker $broker */
        $broker = clone $this->container->getByType(Broker::class);

        /** @var Standard $printer */
        $printer = clone $this->container->getByType(Standard::class);

        /** @var TypeSpecifier $typeSpecifier */
        $typeSpecifier = clone $this->container->getByType(TypeSpecifier::class);

        /** @var ScopeFactory $scopeFactory */
        $scopeFactory = clone $this->container->getByType(ScopeFactory::class);

        $scopeContext = ScopeContext::create($this->currentFileProvider->getSplFileInfo()->getRealPath());

        // Reset scope for new file
        $this->scope = new Scope($scopeFactory, $broker, $printer, $typeSpecifier, $scopeContext);
    }

    /**
     * @return int|null|Node
     */
    public function enterNode(Node $node)
    {
        $this->nodeScopeResolver->setAnalysedFiles([$this->currentFileProvider->getSplFileInfo()->getRealPath()]);

        // it needs to be done here, because all other NodeVisitors enterNode() methods have to be run
        $this->nodeScopeResolver->processNodes([$node], $this->scope, function (Node $node, Scope $scope): void {
            // Record scope
            $this->scope = $scope;
            $node->setAttribute(Attribute::SCOPE, $scope);
        });

        return $node;
    }
}
