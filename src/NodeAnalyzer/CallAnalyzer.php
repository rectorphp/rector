<?php

declare(strict_types=1);

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
    private const OBJECT_CALLS = [MethodCall::class, NullsafeMethodCall::class, StaticCall::class];

    public function isObjectCall(Expr $expr): bool
    {
        if ($expr instanceof BooleanNot) {
            $expr = $expr->expr;
        }

        if ($expr instanceof BinaryOp) {
            $isObjectCallLeft = $this->isObjectCall($expr->left);
            $isObjectCallRight = $this->isObjectCall($expr->right);

            return $isObjectCallLeft || $isObjectCallRight;
        }

        foreach (self::OBJECT_CALLS as $objectCall) {
            if (is_a($expr, $objectCall, true)) {
                return true;
            }
        }

        return false;
    }
}
