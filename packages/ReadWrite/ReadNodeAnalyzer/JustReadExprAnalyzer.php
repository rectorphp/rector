<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Expr;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class JustReadExprAnalyzer
{
    public function isReadContext(Expr $expr) : bool
    {
        if ($expr->getAttribute(AttributeKey::IS_RETURN_EXPR) === \true) {
            return \true;
        }
        if ($expr->getAttribute(AttributeKey::IS_ARG_VALUE) === \true) {
            return \true;
        }
        return $expr->getAttribute(AttributeKey::IS_BEING_ASSIGNED) !== \true;
    }
}
