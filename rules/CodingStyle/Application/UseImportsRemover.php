<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Application;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Configuration\RectorConfigProvider;
final class UseImportsRemover
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    public function __construct(RectorConfigProvider $rectorConfigProvider)
    {
        $this->rectorConfigProvider = $rectorConfigProvider;
    }
    /**
     * @param Stmt[] $stmts
     * @param string[] $removedShortUses
     * @return Stmt[]
     */
    public function removeImportsFromStmts(array $stmts, array $removedShortUses) : array
    {
        /**
         * Verify import name to cover conflict on rename+import,
         * but without $rectorConfig->removeUnusedImports() used
         */
        if (!$this->rectorConfigProvider->shouldImportNames()) {
            return $stmts;
        }
        foreach ($stmts as $stmtKey => $stmt) {
            if (!$stmt instanceof Use_) {
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
    private function removeUseFromUse(array $removedShortUses, Use_ $use) : void
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
