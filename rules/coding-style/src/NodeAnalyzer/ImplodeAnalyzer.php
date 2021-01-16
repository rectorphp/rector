<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeNameResolver\NodeNameResolver;

final class ImplodeAnalyzer
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
     * Matches: "implode('","', $items)"
     */
    public function isImplodeToJson(Expr $expr): bool
    {
        if (! $expr instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($expr, 'implode')) {
            return false;
        }

        if (! isset($expr->args[1])) {
            return false;
        }

        $firstArgumentValue = $expr->args[0]->value;
        if ($firstArgumentValue instanceof String_ && $firstArgumentValue->value !== '","') {
            return false;
        }

        return true;
    }
}
