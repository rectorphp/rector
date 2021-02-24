<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
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
    private const OBJECT_CALLS = [
        MethodCall::class,
        NullsafeMethodCall::class,
        StaticCall::class
    ];

    public function isObjectCall(Node $node): bool
    {
        if ($node instanceof BooleanNot) {
            $node = $node->expr;
        }

        if ($node instanceof BinaryOp) {
            $isObjectCallLeft  = $this->isObjectCall($node->left);
            $isObjectCallRight = $this->isObjectCall($node->right);

            return $isObjectCallLeft || $isObjectCallRight;
        }

        foreach (self::OBJECT_CALLS as $objectCall) {
            if (is_a($node, $objectCall, true)) {
                return true;
            }
        }

        return false;
    }
}
