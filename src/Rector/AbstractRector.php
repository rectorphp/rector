<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Deprecation\DeprecationInterface;
use Rector\Contract\Rector\RectorInterface;

abstract class AbstractRector extends NodeVisitorAbstract implements DeprecationInterface, RectorInterface
{
    public function enterNode(Node $node): ?int
    {
        if ($this->isCandidate($node)) {
            $this->refactor($node);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }
}
