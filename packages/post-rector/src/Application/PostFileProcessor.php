<?php

declare(strict_types=1);

namespace Rector\PostRector\Application;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PostRector\Contract\Rector\PostRectorInterface;

final class PostFileProcessor
{
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];

    /**
     * @param PostRectorInterface[] $postRectors
     */
    public function __construct(array $postRectors)
    {
        $this->postRectors = $this->sortByPriority($postRectors);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->postRectors as $postRector) {
            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor($postRector);
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }

    /**
     * @param PostRectorInterface[] $postRectors
     * @return PostRectorInterface[]
     */
    private function sortByPriority(array $postRectors): array
    {
        $postRectorsByPriority = [];

        foreach ($postRectors as $postRector) {
            if (isset($postRectorsByPriority[$postRector->getPriority()])) {
                throw new ShouldNotHappenException();
            }

            $postRectorsByPriority[$postRector->getPriority()] = $postRector;
        }

        krsort($postRectorsByPriority);

        return $postRectorsByPriority;
    }
}
