<?php

declare (strict_types=1);
namespace Rector\Order;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @see \Rector\Tests\Order\StmtOrderTest
 */
final class StmtOrder
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<int, string> $desiredStmtOrder
     * @param array<int, string> $currentStmtOrder
     * @return array<int, int>
     */
    public function createOldToNewKeys(array $desiredStmtOrder, array $currentStmtOrder) : array
    {
        $newKeys = [];
        foreach ($desiredStmtOrder as $singleDesiredStmtOrder) {
            foreach ($currentStmtOrder as $currentKey => $classMethodName) {
                if ($classMethodName === $singleDesiredStmtOrder) {
                    $newKeys[] = $currentKey;
                }
            }
        }
        $oldKeys = \array_values($newKeys);
        \sort($oldKeys);
        /** @var array<int, int> $oldToNewKeys */
        $oldToNewKeys = \array_combine($oldKeys, $newKeys);
        return $oldToNewKeys;
    }
    /**
     * @param array<int, int> $oldToNewKeys
     */
    public function reorderClassStmtsByOldToNewKeys(\PhpParser\Node\Stmt\ClassLike $classLike, array $oldToNewKeys) : void
    {
        $reorderedStmts = [];
        $stmtCount = \count($classLike->stmts);
        foreach ($classLike->stmts as $key => $stmt) {
            if (!\array_key_exists($key, $oldToNewKeys)) {
                $reorderedStmts[$key] = $stmt;
                continue;
            }
            // reorder here
            $newKey = $oldToNewKeys[$key];
            $reorderedStmts[$key] = $classLike->stmts[$newKey];
        }
        for ($i = 0; $i < $stmtCount; ++$i) {
            if (!\array_key_exists($i, $reorderedStmts)) {
                continue;
            }
            $classLike->stmts[$i] = $reorderedStmts[$i];
        }
    }
    /**
     * @param class-string<Node> $type
     * @return array<int, string>
     */
    public function getStmtsOfTypeOrder(\PhpParser\Node\Stmt\ClassLike $classLike, string $type) : array
    {
        $stmtsByPosition = [];
        foreach ($classLike->stmts as $position => $classStmt) {
            if (!\is_a($classStmt, $type)) {
                continue;
            }
            $name = $this->nodeNameResolver->getName($classStmt);
            if ($name === null) {
                continue;
            }
            $stmtsByPosition[$position] = $name;
        }
        return $stmtsByPosition;
    }
}
