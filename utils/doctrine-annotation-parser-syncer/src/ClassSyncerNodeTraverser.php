<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class ClassSyncerNodeTraverser extends NodeTraverser
{
    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @param ClassSyncerRectorInterface[] $classSyncerRectors
     */
    public function __construct(array $classSyncerRectors, PostFileProcessor $postFileProcessor)
    {
        foreach ($classSyncerRectors as $classSyncerRector) {
            /** @var ClassSyncerRectorInterface&NodeVisitor $classSyncerRector */
            $this->addVisitor($classSyncerRector);
        }

        $this->postFileProcessor = $postFileProcessor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        $traversedNodes = parent::traverse($nodes);
        return $this->postFileProcessor->traverse($traversedNodes);
    }
}
