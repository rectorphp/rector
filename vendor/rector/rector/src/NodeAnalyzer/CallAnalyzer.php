<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
final class CallAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const OBJECT_CALL_TYPES = [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\NullsafeMethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    public function isObjectCall(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot) {
            $expr = $expr->expr;
        }
        if ($expr instanceof \PhpParser\Node\Expr\BinaryOp) {
            $isObjectCallLeft = $this->isObjectCall($expr->left);
            $isObjectCallRight = $this->isObjectCall($expr->right);
            return $isObjectCallLeft || $isObjectCallRight;
        }
        foreach (self::OBJECT_CALL_TYPES as $objectCallType) {
            if (\is_a($expr, $objectCallType, \true)) {
                return \true;
            }
        }
        return \false;
    }
}
