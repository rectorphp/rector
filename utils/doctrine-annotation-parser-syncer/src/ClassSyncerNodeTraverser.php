<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer;

use PhpParser\NodeTraverser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class ClassSyncerNodeTraverser extends NodeTraverser
{
    /**
     * @param ClassSyncerRectorInterface[] $classSyncerRectors
     */
    public function __construct(array $classSyncerRectors)
    {
        foreach ($classSyncerRectors as $classSyncerRector) {
            $this->addVisitor($classSyncerRector);
        }
    }
}
