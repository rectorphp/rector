<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeResolver;

use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class CurrentStmtResolver
{
    public function resolve(Stmt $stmt): Stmt
    {
        $currentStatement = $stmt->getAttribute(AttributeKey::CURRENT_STATEMENT);
        return $currentStatement instanceof Stmt
            ? $currentStatement
            : $stmt;
    }
}
