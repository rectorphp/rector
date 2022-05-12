<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class UnreachableStmtAnalyzer
{
    public function isStmtPHPStanUnreachable(\PhpParser\Node\Stmt $stmt) : bool
    {
        if ($stmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE) === \true) {
            // here the scope is never available for next stmt so we have nothing to refresh
            return \true;
        }
        $previousStmt = $stmt;
        while ($previousStmt = $previousStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE)) {
            if (!$previousStmt instanceof \PhpParser\Node) {
                break;
            }
            if ($previousStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_UNREACHABLE) === \true) {
                return \true;
            }
        }
        return \false;
    }
}
