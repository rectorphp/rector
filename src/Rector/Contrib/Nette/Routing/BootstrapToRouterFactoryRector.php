<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Routing;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use Rector\FileSystem\CurrentFileProvider;
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

    public function __construct(CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
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

        if (! $this->isContainerRouterArrayAssign($node->expr)) {
            return false;
        }

        // @todo clean $container->router = new RouteList;

        return $node->expr->var->var->name->name === 'router';
    }

    /**
     * Collect new Route(...) and remove from origin file
     *
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        $this->collectedRouteNodes[] = $expressionNode->expr->var;

        $this->shouldRemoveNode = true;

        return null;
    }

    private function isBootstrapFile(): bool
    {
        $fileInfo = $this->currentFileProvider->getCurrentFile();

        return $fileInfo->getFilename() === self::BOOTSTRAP_FILE_NAME;
    }

    /**
     * Detects "$container->router[] = "
     */
    private function isContainerRouterArrayAssign(Expr $exprNode): bool
    {
        if (! $exprNode instanceof Assign) {
            return false;
        }

        if (! $exprNode->var instanceof ArrayDimFetch) {
            return false;
        }

        return $exprNode->var->var->var->name === 'container';
    }
}
