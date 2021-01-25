<?php

declare(strict_types=1);

namespace Rector\Renaming\NodeManipulator;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;

final class SwitchManipulator
{
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function removeBreakNodes(array $stmts): array
    {
        foreach ($stmts as $key => $node) {
            if ($node instanceof Break_) {
                unset($stmts[$key]);
            }
        }

        return $stmts;
    }
}
