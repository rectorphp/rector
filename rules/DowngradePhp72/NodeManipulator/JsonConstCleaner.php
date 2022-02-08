<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class JsonConstCleaner
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
    /**
     * @param string[] $constants
     * @param \PhpParser\Node\Expr\BinaryOp\BitwiseOr|\PhpParser\Node\Expr\ConstFetch $node
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\ConstFetch|null
     */
    public function clean($node, array $constants)
    {
        if ($node instanceof \PhpParser\Node\Expr\ConstFetch) {
            return $this->cleanByConstFetch($node, $constants);
        }
        return $this->cleanByBitwiseOr($node, $constants);
    }
    /**
     * @param string[] $constants
     */
    private function cleanByConstFetch(\PhpParser\Node\Expr\ConstFetch $constFetch, array $constants) : ?\PhpParser\Node\Expr\ConstFetch
    {
        if (!$this->nodeNameResolver->isNames($constFetch, $constants)) {
            return null;
        }
        $parent = $constFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
            return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('0'));
        }
        return null;
    }
    /**
     * @param string[] $constants
     */
    private function cleanByBitwiseOr(\PhpParser\Node\Expr\BinaryOp\BitwiseOr $bitwiseOr, array $constants) : ?\PhpParser\Node\Expr
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
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('0'));
    }
    /**
     * @param string[] $constants
     */
    private function isTransformed(\PhpParser\Node\Expr $expr, array $constants) : bool
    {
        return $expr instanceof \PhpParser\Node\Expr\ConstFetch && $this->nodeNameResolver->isNames($expr, $constants);
    }
}
