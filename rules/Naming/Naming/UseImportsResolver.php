<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;

final class UseImportsResolver
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @return Use_[]
     */
    public function resolveForNode(Node $node): array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes(
            $node,
            [Namespace_::class, FileWithoutNamespace::class]
        );
        if (! $namespace instanceof Node) {
            return [];
        }

        return $this->betterNodeFinder->findInstanceOf($namespace, Use_::class);
    }
}
