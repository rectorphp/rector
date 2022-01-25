<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeNameResolver\NodeNameResolver;
final class BitwiseFlagCleaner
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function cleanFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\BinaryOp\BitwiseOr $bitwiseOr, \PhpParser\Node\Expr $expr = null, string $flag) : void
    {
        if ($bitwiseOr->left instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
            /** @var BitwiseOr $leftLeft */
            $leftLeft = $bitwiseOr->left;
            if ($leftLeft->left instanceof \PhpParser\Node\Expr\ConstFetch && $this->nodeNameResolver->isName($leftLeft->left, $flag)) {
                $bitwiseOr = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($leftLeft->right, $bitwiseOr->right);
            }
            /** @var BitwiseOr $leftRight */
            $leftRight = $bitwiseOr->left;
            if ($leftRight->right instanceof \PhpParser\Node\Expr\ConstFetch && $this->nodeNameResolver->isName($leftRight->right, $flag)) {
                $bitwiseOr = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($leftRight->left, $bitwiseOr->right);
            }
            if ($bitwiseOr->left instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
                $this->cleanFuncCall($funcCall, $bitwiseOr->left, $bitwiseOr->right, $flag);
                return;
            }
        }
        if ($expr instanceof \PhpParser\Node\Expr) {
            $bitwiseOr = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($bitwiseOr, $expr);
        }
        $this->assignThirdArgsValue($funcCall, $bitwiseOr, $flag);
    }
    private function assignThirdArgsValue(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\BinaryOp\BitwiseOr $bitwiseOr, string $flag) : void
    {
        if ($bitwiseOr->right instanceof \PhpParser\Node\Expr\ConstFetch && $this->nodeNameResolver->isName($bitwiseOr->right, $flag)) {
            $bitwiseOr = $bitwiseOr->left;
        } elseif ($bitwiseOr->left instanceof \PhpParser\Node\Expr\ConstFetch && $this->nodeNameResolver->isName($bitwiseOr->left, $flag)) {
            $bitwiseOr = $bitwiseOr->right;
        }
        if (!$funcCall->args[3] instanceof \PhpParser\Node\Arg) {
            return;
        }
        $funcCall->args[3]->value = $bitwiseOr;
    }
}
