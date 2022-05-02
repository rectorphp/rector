<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class JsonConstCleaner
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param string[] $constants
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr|null
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
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\BinaryOp\BitwiseOr $node
     */
    private function hasDefinedCheck($node, array $constants) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($node, function (\PhpParser\Node $subNode) use($constants) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode, 'defined')) {
                return \false;
            }
            $args = $subNode->getArgs();
            if (!isset($args[0])) {
                return \false;
            }
            if (!$args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
                return \false;
            }
            return \in_array($args[0]->value->value, $constants, \true);
        });
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
        if ($parent instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
            return null;
        }
        if ($this->hasDefinedCheck($constFetch, $constants)) {
            return null;
        }
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('0'));
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
        if ($this->hasDefinedCheck($bitwiseOr, $constants)) {
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
