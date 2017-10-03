<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Routing;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;
use Rector\FileSystem\CurrentFileProvider;
use Rector\Rector\AbstractRector;

/**
 * Cleanup Rector to @see BootstrapToRouterFactoryRector
 */
final class CleanupBootstrapToRouterFactoryRector extends AbstractRector
{
    /**
     * @var string
     */
    private const BOOTSTRAP_FILE_NAME = 'bootstrap.php';

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

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

        return $this->isContainerRouterAssign($node->expr);
    }

    /**
     * Collect new Route(...) and remove from origin file
     *
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        $this->shouldRemoveNode = true;

        return null;
    }

    private function isBootstrapFile(): bool
    {
        $fileInfo = $this->currentFileProvider->getCurrentFile();

        return $fileInfo->getFilename() === self::BOOTSTRAP_FILE_NAME;
    }

    /**
     * Detects "$container->router = "
     */
    private function isContainerRouterAssign(Expr $exprNode): bool
    {
        if (! $exprNode instanceof Assign) {
            return false;
        }

        if (! $exprNode->var instanceof PropertyFetch) {
            return false;
        }

        if ($exprNode->var->var->name !== 'container') {
            return false;
        }

        return $exprNode->var->name->name === 'router';
    }
}
