<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
/**
 * @deprecated Since 1.1.2, as related rule creates inverted conditions and makes code much less readable.
 */
final class SimpleScalarAnalyzer
{
    public function isSimpleScalar(Expr $expr) : bool
    {
        // empty array
        if ($expr instanceof Array_ && $expr->items === []) {
            return \true;
        }
        // empty string
        return $expr instanceof String_ && $expr->value === '';
    }
}
