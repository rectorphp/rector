<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeNameResolver\NodeNameResolver;

final class BitwiseFlagCleaner
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function cleanFuncCall(FuncCall $funcCall, BitwiseOr $bitwiseOr, Expr $expr = null, string $flag): void
    {
        if ($bitwiseOr->left instanceof BitwiseOr) {
            /** @var BitwiseOr $leftLeft */
            $leftLeft = $bitwiseOr->left;
            if ($leftLeft->left instanceof ConstFetch && $this->nodeNameResolver
                ->isName($leftLeft->left, $flag)) {
                $bitwiseOr = new BitwiseOr($leftLeft->right, $bitwiseOr->right);
            }

            /** @var BitwiseOr $leftRight */
            $leftRight = $bitwiseOr->left;
            if ($leftRight->right instanceof ConstFetch && $this->nodeNameResolver
                ->isName($leftRight->right, $flag)) {
                $bitwiseOr = new BitwiseOr($leftRight->left, $bitwiseOr->right);
            }

            if ($bitwiseOr->left instanceof BitwiseOr) {
                $this->cleanFuncCall($funcCall, $bitwiseOr->left, $bitwiseOr->right, $flag);
                return;
            }
        }

        if ($expr instanceof Expr) {
            $bitwiseOr = new BitwiseOr($bitwiseOr, $expr);
        }

        $this->assignThirdArgsValue($funcCall, $bitwiseOr, $flag);
    }

    private function assignThirdArgsValue(FuncCall $funcCall, BitwiseOr $bitwiseOr, string $flag): void
    {
        if ($bitwiseOr->right instanceof ConstFetch && $this->nodeNameResolver
            ->isName($bitwiseOr->right, $flag)) {
            $bitwiseOr = $bitwiseOr->left;
        } elseif ($bitwiseOr->left instanceof ConstFetch && $this->nodeNameResolver
            ->isName($bitwiseOr->left, $flag)) {
            $bitwiseOr = $bitwiseOr->right;
        }

        if (! $funcCall->args[3] instanceof Arg) {
            return;
        }

        $funcCall->args[3]->value = $bitwiseOr;
    }
}
