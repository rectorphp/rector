<?php

declare(strict_types=1);

namespace Rector\Order;

use PhpParser\Node\Stmt\Class_;

final class StmtOrder
{
    /**
     * @param string[] $desiredStmtOrder
     * @return int[]
     */
    public function createOldToNewKeys(array $desiredStmtOrder, array $currentStmtOrder): array
    {
        $newKeys = [];
        foreach ($desiredStmtOrder as $desiredClassMethod) {
            foreach ($currentStmtOrder as $currentKey => $classMethodName) {
                if ($classMethodName === $desiredClassMethod) {
                    $newKeys[] = $currentKey;
                }
            }
        }

        $oldKeys = array_values($newKeys);
        sort($oldKeys);

        return array_combine($oldKeys, $newKeys);
    }

    public function reorderClassStmtsByOldToNewKeys(Class_ $node, array $oldToNewKeys): Class_
    {
        $reorderedStmts = [];

        $stmtCount = count($node->stmts);

        foreach ($node->stmts as $key => $stmt) {
            if (! array_key_exists($key, $oldToNewKeys)) {
                $reorderedStmts[$key] = $stmt;
                continue;
            }

            // reorder here
            $newKey = $oldToNewKeys[$key];

            $reorderedStmts[$newKey] = $stmt;
        }

        for ($i = 0; $i < $stmtCount; ++$i) {
            if (! array_key_exists($i, $reorderedStmts)) {
                continue;
            }

            $node->stmts[$i] = $reorderedStmts[$i];
        }

        return $node;
    }
}
