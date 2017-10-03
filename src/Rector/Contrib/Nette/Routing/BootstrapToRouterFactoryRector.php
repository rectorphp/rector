<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Routing;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
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
     * Matches $container->router[] =
     */
    public function isCandidate(Node $node): bool
    {
        $fileInfo = $this->currentFileProvider->getCurrentFile();
        if ($fileInfo->getFilename() !== self::BOOTSTRAP_FILE_NAME) {
            return false;
        }

        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->var instanceof ArrayDimFetch) {
            return false;
        }

        if ($node->var->var->var->name !== 'container') {
            return false;
        }

        return $node->var->var->name->name === 'router';
    }

    /**
     * Collect new Route(...) and remove
     *
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        $this->collectedRouteNodes[] = $assignNode->var;

        return null;
    }
}
