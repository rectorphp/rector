<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
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

        $collectedUses = [];

        foreach ($namespace->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                $collectedUses[] = $stmt;
                continue;
            }

            if ($stmt instanceof GroupUse) {
                $groupUseUses = [];
                foreach ($stmt->uses as $useUse) {
                    $groupUseUses[] = new UseUse(
                        new Name($stmt->prefix . '\\' . $useUse->name),
                        $useUse->alias,
                        $useUse->type,
                        $useUse->getAttributes()
                    );
                }

                $collectedUses[] = new Use_($groupUseUses, $stmt->type, $stmt->getAttributes());
            }
        }

        return $collectedUses;
    }
}
