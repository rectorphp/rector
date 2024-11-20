<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\Int_;
use Rector\Enum\JsonConstant;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function clean($node, array $constants) : ?\PhpParser\Node\Expr
    {
        if ($node instanceof BitwiseOr) {
            return $this->cleanByBitwiseOr($node, $constants);
        }
        return $this->cleanByConstFetch($node, $constants);
    }
    /**
     * @param array<JsonConstant::*> $constants
     */
    private function cleanByConstFetch(ConstFetch $constFetch, array $constants) : ?Int_
    {
        if (!$this->nodeNameResolver->isNames($constFetch, $constants)) {
            return null;
        }
        return new Int_(0);
    }
    /**
     * @param array<JsonConstant::*> $constants
     * @return null|\PhpParser\Node\Expr|\PhpParser\Node\Scalar\Int_
     */
    private function cleanByBitwiseOr(BitwiseOr $bitwiseOr, array $constants)
    {
        $isLeftTransformed = $this->isTransformed($bitwiseOr->left, $constants);
        $isRightTransformed = $this->isTransformed($bitwiseOr->right, $constants);
        if (!$isLeftTransformed && !$isRightTransformed) {
            return null;
        }
        if (!$isLeftTransformed) {
            return $bitwiseOr->left;
        }
        if (!$isRightTransformed) {
            return $bitwiseOr->right;
        }
        return new Int_(0);
    }
    /**
     * @param string[] $constants
     */
    private function isTransformed(Expr $expr, array $constants) : bool
    {
        if ($expr instanceof ConstFetch && $this->nodeNameResolver->isNames($expr, $constants)) {
            return \true;
        }
        return !$expr->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node;
    }
}
