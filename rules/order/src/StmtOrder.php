<?php

declare(strict_types=1);

namespace Rector\Order;

use PhpParser\Node\Stmt\ClassLike;

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

    public function reorderClassStmtsByOldToNewKeys(ClassLike $classLike, array $oldToNewKeys): ClassLike
    {
        $reorderedStmts = [];

        $stmtCount = count($classLike->stmts);

        foreach ($classLike->stmts as $key => $stmt) {
            if (! array_key_exists($key, $oldToNewKeys)) {
                $reorderedStmts[$key] = $stmt;
                continue;
            }

            // reorder here
            $newKey = $oldToNewKeys[$key];

            $reorderedStmts[$key] = $classLike->stmts[$newKey];
        }

        for ($i = 0; $i < $stmtCount; ++$i) {
            if (! array_key_exists($i, $reorderedStmts)) {
                continue;
            }

            $classLike->stmts[$i] = $reorderedStmts[$i];
        }

        return $classLike;
    }
}
