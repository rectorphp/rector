<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Deprecation\DeprecationInterface;
use Rector\Contract\Rector\RectorInterface;

abstract class AbstractRector extends NodeVisitorAbstract implements DeprecationInterface, RectorInterface
{
    /**
     * @return null|int|Node
     */
    public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            if ($newNode = $this->refactor($node)) {
                return $newNode;
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }
}
