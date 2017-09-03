<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\Deprecation\DeprecationInterface;
use Rector\Contract\Rector\RectorInterface;

abstract class AbstractRector extends NodeVisitorAbstract implements DeprecationInterface, RectorInterface
{
    /**
     * @var Class_|null
     */
    protected $classNode;

    /**
     * @param Node[] $nodes
     * @return null|Node[]
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classNode = null;

        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->classNode = $node;
                break;
            }
        }

        return null;
    }

    protected function getClassName(): string
    {
        return $this->classNode->namespacedName->toString();
    }

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
