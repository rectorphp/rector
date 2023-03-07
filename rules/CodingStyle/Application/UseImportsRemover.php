<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeRemoval\NodeRemover;
final class UseImportsRemover
{
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(NodeRemover $nodeRemover)
    {
        $this->nodeRemover = $nodeRemover;
    }
    /**
     * @param Stmt[] $stmts
     * @param string[] $removedUses
     */
    public function removeImportsFromStmts(array $stmts, array $removedUses) : void
    {
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            $this->removeUseFromUse($removedUses, $stmt);
        }
    }
    /**
     * @param string[] $removedUses
     */
    private function removeUseFromUse(array $removedUses, Use_ $use) : void
    {
        foreach ($use->uses as $usesKey => $useUse) {
            foreach ($removedUses as $removedUse) {
                if ($useUse->name->toString() === $removedUse) {
                    unset($use->uses[$usesKey]);
                }
            }
        }
        if ($use->uses === []) {
            $this->nodeRemover->removeNode($use);
        }
    }
}
