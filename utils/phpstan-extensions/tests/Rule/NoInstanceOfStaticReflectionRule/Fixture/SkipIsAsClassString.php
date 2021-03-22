<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

final class SkipIsAsClassString
{
    /**
     * @param class-string<Node> $desiredType
     */
    private function hasOnlyStmtOfType(If_ $if, string $desiredType): bool
    {
        $stmts = $if->stmts;
        return is_a($stmts[0], $desiredType);
    }
}
