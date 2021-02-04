<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use Rector\NodeNameResolver\NodeNameResolver;

final class ForNodeAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Expr[] $condExprs
     */
    public function isCondExprSmallerOrGreater(array $condExprs, string $keyValueName, string $countValueName): bool
    {
        // $i < $count
        if ($condExprs[0] instanceof Smaller) {
            if (! $this->nodeNameResolver->isName($condExprs[0]->left, $keyValueName)) {
                return false;
            }

            return $this->nodeNameResolver->isName($condExprs[0]->right, $countValueName);
        }

        // $i > $count
        if ($condExprs[0] instanceof Greater) {
            if (! $this->nodeNameResolver->isName($condExprs[0]->left, $countValueName)) {
                return false;
            }

            return $this->nodeNameResolver->isName($condExprs[0]->right, $keyValueName);
        }

        return false;
    }
}
