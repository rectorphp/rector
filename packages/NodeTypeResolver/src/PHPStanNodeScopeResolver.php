<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\ContainerFactory;
use Rector\Configuration\Option;
use Rector\FileSystem\FilesFinder;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Configuration\CurrentFileProvider;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

/**
 * @inspired by https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php
 * - https://github.com/silverstripe/silverstripe-upgrader/pull/57/commits/e5c7cfa166ad940d9d4ff69537d9f7608e992359#diff-5e0807bb3dc03d6a8d8b6ad049abd774
 */
final class PHPStanNodeScopeResolver
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
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var Broker
     */
    private $phpstanBroker;

    /**
     * @var Standard
     */
    private $phpstanPrinter;

    /**
     * @var TypeSpecifier
     */
    private $phpstanTypeSpecifier;

    /**
     * @var ScopeFactory
     */
    private $phpstanScopeFactory;

    public function __construct(
        CurrentFileProvider $currentFileProvider,
        ParameterProvider $parameterProvider,
        FilesFinder $filesFinder
    ) {
        $phpstanContainer = (new ContainerFactory(getcwd()))->create(sys_get_temp_dir(), []);

        $this->phpstanBroker = $phpstanContainer->getByType(Broker::class);
        $this->phpstanPrinter = $phpstanContainer->getByType(Standard::class);
        $this->nodeScopeResolver = $phpstanContainer->getByType(NodeScopeResolver::class);
        $this->phpstanTypeSpecifier = $phpstanContainer->getByType(TypeSpecifier::class);
        $this->phpstanScopeFactory = $phpstanContainer->getByType(ScopeFactory::class);

        $this->currentFileProvider = $currentFileProvider;
        $this->parameterProvider = $parameterProvider;
        $this->filesFinder = $filesFinder;
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function processNodes(array $nodes): array
    {
        $this->resolveNamespacedNamesForNodes($nodes);

        $this->scope = $this->createScopeByFile($this->currentFileProvider->getSplFileInfo());

        $this->setAnalysedFiles();

        $this->nodeScopeResolver->processNodes(
            $nodes,
            $this->scope,
            function (Node $node, Scope $scope): void {
                $this->scope = $scope;
                $node->setAttribute(Attribute::SCOPE, $scope);
            }
        );

        return $nodes;
    }

    private function createScopeByFile(SplFileInfo $splFileInfo): Scope
    {
        $scopeContext = ScopeContext::create($splFileInfo->getRealPath());

        // Reset scope for new file
        return new Scope(
            $this->phpstanScopeFactory,
            $this->phpstanBroker,
            $this->phpstanPrinter,
            $this->phpstanTypeSpecifier,
            $scopeContext
        );
    }

    /**
     * @param Stmt[] $nodes
     */
    private function resolveNamespacedNamesForNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->traverse($nodes);
    }

    private function setAnalysedFiles(): void
    {
        $source = $this->parameterProvider->provideParameter(Option::SOURCE);
        $phpFiles = $this->filesFinder->findInDirectoriesAndFiles($source, ['php']);
        $this->nodeScopeResolver->setAnalysedFiles($phpFiles);
    }
}
