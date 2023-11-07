<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use Rector\Renaming\Collector\RenamedNameCollector;
final class UseImportsRemover
{
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\RenamedNameCollector
     */
    private $renamedNameCollector;
    public function __construct(RenamedNameCollector $renamedNameCollector)
    {
        $this->renamedNameCollector = $renamedNameCollector;
    }
    /**
     * @param Stmt[] $stmts
     * @param string[] $removedUses
     * @return Stmt[]
     */
    public function removeImportsFromStmts(array $stmts, array $removedUses) : array
    {
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            $stmt = $this->removeUseFromUse($removedUses, $stmt);
            // remove empty uses
            if ($stmt->uses === []) {
                unset($stmts[$key]);
            }
        }
        return $stmts;
    }
    /**
     * @param string[] $removedUses
     */
    private function removeUseFromUse(array $removedUses, Use_ $use) : Use_
    {
        foreach ($use->uses as $usesKey => $useUse) {
            $useName = $useUse->name->toString();
            if (!\in_array($useName, $removedUses, \true)) {
                continue;
            }
            if (!$this->renamedNameCollector->has($useName)) {
                continue;
            }
            unset($use->uses[$usesKey]);
        }
        return $use;
    }
}
