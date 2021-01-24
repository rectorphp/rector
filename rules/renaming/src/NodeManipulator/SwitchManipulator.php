<?php

declare(strict_types=1);

namespace Rector\Renaming\NodeManipulator;

use PhpParser\Node\Stmt;

final class SwitchManipulator
{
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function removeBreakNodes(array $stmts): array
    {
        foreach ($stmts as $key => $node) {
            if ($node instanceof Stmt\Break_) {
                unset($stmts[$key]);
            }
        }

        return $stmts;
    }
}
