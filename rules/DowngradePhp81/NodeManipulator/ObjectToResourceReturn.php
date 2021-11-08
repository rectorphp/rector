<?php

declare(strict_types=1);

namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\NodeFactory;

final class ObjectToResourceReturn
{
    public function __construct(
        private NodeFactory $nodeFactory
    ) {
    }

    /**
     * @param string[] $collectionObjectToResource
     */
    public function refactor(Instanceof_ $instanceof, array $collectionObjectToResource): ?FuncCall
    {
        if (! $instanceof->class instanceof FullyQualified) {
            return null;
        }

        $className = $instanceof->class->toString();
        foreach ($collectionObjectToResource as $singleCollectionObjectToResource) {
            if ($singleCollectionObjectToResource === $className) {
                return $this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]);
            }
        }

        return null;
    }
}
