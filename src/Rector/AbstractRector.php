<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Rector\RectorInterface;

abstract class AbstractRector extends NodeVisitorAbstract implements RectorInterface
{
    /**
     * @var bool
     */
    protected $shouldRemoveNode = false;

    /**
     * @return null|int|Node
     */
    final public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            if ($newNode = $this->refactor($node)) {
                return $newNode;
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    /**
     * @return null|int|Node
     */
    final public function leaveNode(Node $node)
    {
        if ($this->shouldRemoveNode) {
            $this->shouldRemoveNode = false;

            return new Nop;
        }

        return null;
    }
}
