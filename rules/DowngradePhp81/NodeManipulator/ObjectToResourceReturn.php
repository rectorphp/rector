<?php

declare(strict_types=1);

namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;

final class ObjectToResourceReturn
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
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
            if ($singleCollectionObjectToResource !== $className) {
                continue;
            }

            $binaryOp = $this->betterNodeFinder->findParentType($instanceof, BinaryOp::class);
            if ($this->hasIsResourceCheck($binaryOp)) {
                continue;
            }

            return $this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]);
        }

        return null;
    }

    private function hasIsResourceCheck(?BinaryOp $binaryOp): bool
    {
        if ($binaryOp instanceof BinaryOp) {
            return (bool) $this->betterNodeFinder->findFirst(
                $binaryOp,
                function (Node $subNode): bool {
                    if (! $subNode instanceof FuncCall) {
                        return false;
                    }

                    if (! $subNode->name instanceof Name) {
                        return false;
                    }

                    return $this->nodeNameResolver->isName($subNode->name, 'is_resource');
                }
            );
        }

        return false;
    }
}
