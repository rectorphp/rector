<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeResolver;

use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CurrentStmtResolver
{
    public function resolve(\PhpParser\Node\Stmt $stmt) : \PhpParser\Node\Stmt
    {
        $currentStatement = $stmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        return $currentStatement instanceof \PhpParser\Node\Stmt ? $currentStatement : $stmt;
    }
}
