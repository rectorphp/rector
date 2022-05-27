<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
final class UseImportsRemover
{
    /**
     * @param Stmt[] $stmts
     * @param string[] $removedShortUses
     * @return Stmt[]
     */
    public function removeImportsFromStmts(array $stmts, array $removedShortUses) : array
    {
        foreach ($stmts as $stmtKey => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Use_) {
                continue;
            }
            $this->removeUseFromUse($removedShortUses, $stmt);
            // nothing left → remove
            if ($stmt->uses === []) {
                unset($stmts[$stmtKey]);
            }
        }
        return $stmts;
    }
    /**
     * @param string[] $removedShortUses
     */
    public function removeImportsFromNamespace(\PhpParser\Node\Stmt\Namespace_ $namespace, array $removedShortUses) : void
    {
        foreach ($namespace->stmts as $namespaceKey => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Use_) {
                continue;
            }
            $this->removeUseFromUse($removedShortUses, $stmt);
            // nothing left → remove
            if ($stmt->uses === []) {
                unset($namespace->stmts[$namespaceKey]);
            }
        }
    }
    /**
     * @param string[] $removedShortUses
     */
    private function removeUseFromUse(array $removedShortUses, \PhpParser\Node\Stmt\Use_ $use) : void
    {
        foreach ($use->uses as $usesKey => $useUse) {
            foreach ($removedShortUses as $removedShortUse) {
                if ($useUse->name->toString() === $removedShortUse) {
                    unset($use->uses[$usesKey]);
                }
            }
        }
    }
}
