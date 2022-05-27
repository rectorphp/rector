<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class UnreachableStmtAnalyzer
{
    /**
     * in case of unreachable stmts, no other node will have available scope
     * recursively check previous expressions, until we find nothing or is_unreachable
     */
    public function isStmtPHPStanUnreachable(?Stmt $stmt) : bool
    {
        if (!$stmt instanceof Stmt) {
            return \false;
        }
        if ($stmt->getAttribute(AttributeKey::IS_UNREACHABLE) === \true) {
            // here the scope is never available for next stmt so we have nothing to refresh
            return \true;
        }
        $previousStmt = $stmt->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (!$previousStmt instanceof Node) {
            return \false;
        }
        if ($previousStmt instanceof Stmt) {
            return $this->isStmtPHPStanUnreachable($previousStmt);
        }
        return \true;
    }
}
