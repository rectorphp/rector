<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Routing;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use Rector\Builder\Contrib\Nette\RouterFactoryClassBuilder;
use Rector\FileSystem\CurrentFileProvider;
use Rector\NodeAnalyzer\AssignAnalyzer;
use Rector\NodeVisitor\Collector\NodeCollector;
use Rector\Rector\AbstractRector;

final class BootstrapToRouterFactoryRector extends AbstractRector
{
    /**
     * @var string
     */
    private const BOOTSTRAP_FILE_NAME = 'bootstrap.php';

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    /**
     * @var mixed[]
     */
    private $collectedRouteNodes = [];

    /**
     * @var AssignAnalyzer
     */
    private $assignAnalyzer;

    /**
     * @var RouterFactoryClassBuilder
     */
    private $routerFactoryClassBuilder;

    /**
     * @var Standard
     */
    private $standard;

    /**
     * @var NodeCollector
     */
    private $nodeCollector;

    public function __construct(
        CurrentFileProvider $currentFileProvider,
        AssignAnalyzer $assignAnalyzer,
        RouterFactoryClassBuilder $routerFactoryClassBuilder,
        Standard $standard,
        NodeCollector $nodeCollector
    ) {
        $this->currentFileProvider = $currentFileProvider;
        $this->assignAnalyzer = $assignAnalyzer;
        $this->routerFactoryClassBuilder = $routerFactoryClassBuilder;
        $this->standard = $standard;
        $this->nodeCollector = $nodeCollector;
    }

    /**
     * Matches $container->router[] = new ...;
     */
    public function isCandidate(Node $node): bool
    {
        if (! $this->isBootstrapFile()) {
            return false;
        }

        if (! $node instanceof Expression) {
            return false;
        }

        return $this->assignAnalyzer->isArrayAssignTypeAndProperty(
            $node->expr,
            'Nette\DI\Container',
            'router'
        );
    }

    /**
     * Collect new Route(...) and remove from origin file
     *
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        $this->collectedRouteNodes[] = $expressionNode->expr->expr;

        $this->nodeCollector->addNodeToRemove($expressionNode);

        return null;
    }

    /**
     * @param Node[] $nodes
     */
    public function afterTraverse(array $nodes): void
    {
        $routerFactoryClassNodes = $this->routerFactoryClassBuilder->build($this->collectedRouteNodes);

        $this->collectedRouteNodes = [];

        // save file to same location as bootstrap.php is
        $currentFileInfo = $this->currentFileProvider->getCurrentFile();
        $fileLocation = dirname($currentFileInfo->getRealPath()) . DIRECTORY_SEPARATOR . 'RouterFactory.php';

        file_put_contents(
            $fileLocation,
            $this->standard->prettyPrintFile($routerFactoryClassNodes)
        );
    }

    private function isBootstrapFile(): bool
    {
        $fileInfo = $this->currentFileProvider->getCurrentFile();

        return $fileInfo->getFilename() === self::BOOTSTRAP_FILE_NAME;
    }
}
