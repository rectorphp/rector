<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\Int_;
use Rector\Enum\JsonConstant;
use Rector\NodeNameResolver\NodeNameResolver;
final class JsonConstCleaner
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param array<JsonConstant::*> $constants
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node
     */
    public function clean($node, array $constants): ?\PhpParser\Node\Expr
    {
        if ($node instanceof BitwiseOr) {
            return $this->cleanByBitwiseOr($node, $constants);
        }
        return $this->cleanByConstFetch($node, $constants);
    }
    /**
     * @param array<JsonConstant::*> $constants
     */
    private function cleanByConstFetch(ConstFetch $constFetch, array $constants): ?Int_
    {
        if (!$this->nodeNameResolver->isNames($constFetch, $constants)) {
            return null;
        }
        return new Int_(0);
    }
    /**
     * @param array<JsonConstant::*> $constants
     */
    private function cleanByBitwiseOr(BitwiseOr $bitwiseOr, array $constants): ?Expr
    {
        $keptOperands = [];
        $hasChanged = \false;
        foreach ($this->flattenOperands($bitwiseOr) as $operand) {
            if ($operand instanceof ConstFetch && $this->nodeNameResolver->isNames($operand, $constants)) {
                $hasChanged = \true;
                continue;
            }
            $keptOperands[] = $operand;
        }
        if (!$hasChanged) {
            return null;
        }
        if ($keptOperands === []) {
            return new Int_(0);
        }
        $result = array_shift($keptOperands);
        foreach ($keptOperands as $keptOperand) {
            $result = new BitwiseOr($result, $keptOperand);
        }
        return $result;
    }
    /**
     * Flatten a left-associative bitwise-or chain into its operands. Structural, so dropping a
     * target constant does not depend on the ORIGINAL_NODE attribute, which is not reliable on
     * re-decorated nodes.
     *
     * @return Expr[]
     */
    private function flattenOperands(BitwiseOr $bitwiseOr): array
    {
        $operands = [];
        foreach ([$bitwiseOr->left, $bitwiseOr->right] as $operand) {
            if ($operand instanceof BitwiseOr) {
                $operands = array_merge(is_array($operands) ? $operands : iterator_to_array(is_array($operands) ? new \ArrayIterator($operands) : $operands), $this->flattenOperands($operand));
                continue;
            }
            $operands[] = $operand;
        }
        return $operands;
    }
}
